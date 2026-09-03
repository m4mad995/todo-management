<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    activeTask: Object,
});

const STORAGE_KEY = 'pomodoro-timer';

// Timer state
const workTime = ref(25 * 60);
const breakTime = ref(5 * 60);
const timeLeft = ref(25 * 60);
const isRunning = ref(false);
const isBreak = ref(false);
const presetMode = ref('25/5');
const cyclesCompleted = ref(0);

// Session state
const showSkipConfirm = ref(false);
const showPhaseAlert = ref(false);
const phaseAlertMessage = ref('');

let timerInterval = null;
let endTime = null;

// === localStorage persistence ===
const getTimerState = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch { return null; }
};

const saveTimerState = () => {
    const state = {
        endTime: endTime || null,
        taskId: props.activeTask?.id || null,
        taskTitle: props.activeTask?.title || 'Focus Session',
        taskMatrix: props.activeTask?.matrix || null,
        presetMode: presetMode.value,
        isBreak: isBreak.value,
        cyclesCompleted: cyclesCompleted.value,
        isActive: isRunning.value,
        isCompleted: false,
        widgetDismissed: false,
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
};

const clearTimerState = () => {
    localStorage.removeItem(STORAGE_KEY);
};

const updateTimerState = (overrides = {}) => {
    const current = getTimerState() || {};
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...current, ...overrides }));
};

// === Audio ===
const playBeep = () => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        osc.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.5);
    } catch (e) {
        console.log('Audio error:', e);
    }
};

// === Formatted time ===
const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// === Timer controls ===
const toggleTimer = () => {
    if (isRunning.value) {
        pauseTimer();
    } else {
        startTimer();
    }
};

const startTimer = () => {
    isRunning.value = true;
    endTime = Date.now() + (timeLeft.value * 1000);
    saveTimerState();
    timerInterval = setInterval(() => {
        updateRemainingTime();
    }, 500);
};

const updateRemainingTime = () => {
    if (!endTime) return;
    const remaining = Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
    timeLeft.value = remaining;
    if (remaining <= 0) {
        handlePhaseComplete();
    }
};

const pauseTimer = () => {
    isRunning.value = false;
    clearInterval(timerInterval);
    // endTime TIDAK di-null-kan agar bisa resume saat re-enter session
    updateTimerState({ isActive: false, timeLeft: timeLeft.value });
};

const resetTimer = () => {
    pauseTimer();
    timeLeft.value = isBreak.value ? breakTime.value : workTime.value;
    clearTimerState();
};

const handlePhaseComplete = () => {
    pauseTimer();
    playBeep();

    if (!isBreak.value) {
        cyclesCompleted.value++;
        isBreak.value = true;
        timeLeft.value = breakTime.value;
        phaseAlertMessage.value = 'Sesi kerja selesai! Saatnya istirahat.';
    } else {
        isBreak.value = false;
        timeLeft.value = workTime.value;
        phaseAlertMessage.value = 'Istirahat selesai! Siap untuk fokus lagi?';
    }

    showPhaseAlert.value = true;

    updateTimerState({
        isActive: false,
        isCompleted: true,
        isBreak: isBreak.value,
        cyclesCompleted: cyclesCompleted.value,
    });
};

const dismissPhaseAlert = () => {
    showPhaseAlert.value = false;
    updateTimerState({ widgetDismissed: true });
};

const handleStartBreak = () => {
    showPhaseAlert.value = false;
    startTimer();
};

const setPreset = (workMin, breakMin, modeLabel) => {
    pauseTimer();
    presetMode.value = modeLabel;
    workTime.value = workMin * 60;
    breakTime.value = breakMin * 60;
    isBreak.value = false;
    timeLeft.value = workTime.value;
    clearTimerState();
};

const skipPhase = () => {
    showSkipConfirm.value = false;
    pauseTimer();
    playBeep();
    if (!isBreak.value) {
        cyclesCompleted.value++;
        isBreak.value = true;
        timeLeft.value = breakTime.value;
    } else {
        isBreak.value = false;
        timeLeft.value = workTime.value;
    }
    updateTimerState({
        isBreak: isBreak.value,
        cyclesCompleted: cyclesCompleted.value,
    });
};

const markAsComplete = () => {
    if (props.activeTask) {
        router.patch(`/tasks/${props.activeTask.id}`, {
            completed: true,
        }, {
            onSuccess: () => {
                clearTimerState();
                window.dispatchEvent(new Event('timer-cleared'));
                router.visit('/focus');
            },
        });
    }
};

// === Visibility change ===
const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible' && isRunning.value) {
        updateRemainingTime();
    }
};

// === Load from localStorage on mount ===
onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibilityChange);

    const saved = getTimerState();
    if (saved && !saved.isCompleted) {
        // Restore preset times
        const preset = presets.find(p => p.label === saved.presetMode);
        if (preset) {
            workTime.value = preset.work * 60;
            breakTime.value = preset.break * 60;
        }
        isBreak.value = saved.isBreak || false;
        presetMode.value = saved.presetMode || '25/5';
        cyclesCompleted.value = saved.cyclesCompleted || 0;

        if (saved.isActive && saved.endTime) {
            // Timer was running — resume from endTime
            const remaining = Math.max(0, Math.ceil((saved.endTime - Date.now()) / 1000));
            if (remaining > 0) {
                timeLeft.value = remaining;
                startTimer();
            } else {
                // Timer expired while away
                handlePhaseComplete();
            }
        } else if (saved.timeLeft > 0) {
            // Timer was paused — resume from saved timeLeft
            timeLeft.value = saved.timeLeft;
        } else {
            // No timer state — set default time
            timeLeft.value = workTime.value;
        }
    }
});

onUnmounted(() => {
    clearInterval(timerInterval);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

// === Presets ===
const presets = [
    { work: 25, break: 5, label: '25/5', name: 'Standard' },
    { work: 50, break: 10, label: '50/10', name: 'Extended' },
    { work: 15, break: 3, label: '15/3', name: 'Sprint' },
];

// === Badge quadrant config ===
const matrixConfig = {
    do_first: { label: 'Do First', badgeClass: 'badge-red' },
    schedule: { label: 'Do Next', badgeClass: 'badge-blue' },
    delegate: { label: 'Hand Off', badgeClass: 'badge-amber' },
    drop: { label: 'Ignore', badgeClass: 'badge-gray' },
};

const badgeLabel = computed(() => {
    if (isBreak.value) return 'Istirahat';
    const matrix = props.activeTask?.matrix;
    return matrixConfig[matrix]?.label || 'Fokus';
});

const badgeDescription = computed(() => {
    if (isBreak.value) return 'Istirahat sejenak sebelum lanjut.';
    const matrix = props.activeTask?.matrix;
    const labels = {
        do_first: 'Do First, fokus sampai timer habis!',
        schedule: 'Do Next, fokus sampai timer habis!',
        delegate: 'Hand Off, fokus sampai timer habis!',
        drop: 'Ignore, fokus sampai timer habis!',
    };
    return labels[matrix] || 'Fokus sampai timer habis!';
});

const badgeClass = computed(() => {
    if (isBreak.value) return 'badge-emerald';
    const matrix = props.activeTask?.matrix;
    return matrixConfig[matrix]?.badgeClass || 'badge-blue';
});
</script>

<template>
    <Head title="Focus Session" />

    <AuthenticatedLayout>
        <!-- Back -->
        <div class="mb-5 flex items-center justify-between">
            <Link href="/focus" class="text-gray-500 hover:text-gray-800 font-medium text-[15px] flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </Link>

            <span class="badge-blue">
                Siklus: {{ cyclesCompleted }}
            </span>
        </div>

        <!-- Main Card -->
        <div class="max-w-lg mx-auto card p-5 sm:p-8 text-center">
            <!-- Mode Badge -->
            <span :class="badgeClass" class="mb-4 inline-flex">
                {{ badgeLabel }}
            </span>

            <!-- Task Title -->
            <h1 class="text-xl font-bold text-gray-900 leading-snug mb-1">
                {{ activeTask ? activeTask.title : 'Focus Session' }}
            </h1>
            <p v-if="activeTask" class="text-gray-400 text-[13px] mb-6">
                {{ badgeDescription }}
            </p>
            <div v-else class="mb-6"></div>

            <!-- Preset Selector -->
            <div class="flex items-center justify-center gap-1 p-1 bg-gray-100 dark:bg-slate-700 rounded-btn mb-8 w-fit mx-auto">
                <button
                    v-for="p in presets"
                    :key="p.label"
                    @click="setPreset(p.work, p.break, p.label)"
                    :class="[
                        'px-3 sm:px-4 py-2 rounded-btn text-sm font-medium transition-all duration-150',
                        presetMode === p.label
                            ? 'bg-surface dark:bg-slate-800 text-gray-900 dark:text-gray-100 shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
                    ]"
                >
                    {{ p.name }}
                </button>
            </div>

            <!-- Timer -->
            <div class="my-6">
                <div
                    class="text-5xl sm:text-6xl font-mono font-black tracking-tight transition-colors text-blue-600"
                >
                    {{ formattedTime }}
                </div>
            </div>

            <!-- Controls -->
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 my-6">
                <button
                    @click="toggleTimer"
                    :class="[
                        'btn-lg transition-all duration-150',
                        isRunning
                            ? 'btn-outline border-amber-300 text-amber-700 hover:bg-amber-50'
                            : 'btn-primary'
                    ]"
                >
                    <svg v-if="!isRunning" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ isRunning ? 'Pause' : 'Start Focus' }}
                </button>

                <button @click="resetTimer" class="btn-ghost btn-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset
                </button>

                <button @click="showSkipConfirm = true" class="btn-ghost btn-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                    Skip
                </button>
            </div>

            <!-- Mark Complete -->
            <div v-if="activeTask" class="pt-5 border-t border-gray-100 mt-6">
                <button
                    @click="markAsComplete"
                    class="text-emerald-600 hover:text-emerald-700 font-semibold text-[15px] transition inline-flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Tandai selesai
                </button>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Skip Confirmation Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showSkipConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="showSkipConfirm = false"></div>
                <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-sm animate-slide-up">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[15px] font-bold text-gray-900">Skip Sesi Ini?</h3>
                                <p class="text-[13px] text-gray-500">Timer akan di-skip dan lanjut ke fase berikutnya.</p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                            <button @click="showSkipConfirm = false" class="btn-ghost btn-sm">Batal</button>
                            <button @click="skipPhase" class="btn-primary btn-sm">Skip Sesi</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Phase Complete Alert -->
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showPhaseAlert" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm"></div>
                <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-sm animate-slide-up">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[15px] font-bold text-gray-900">{{ phaseAlertMessage }}</h3>
                                <p class="text-[13px] text-gray-500">Siklus {{ cyclesCompleted }} selesai.</p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                            <button @click="dismissPhaseAlert" class="btn-ghost btn-sm">Nanti Saja</button>
                            <button @click="handleStartBreak" class="btn-primary btn-sm">
                                {{ isBreak ? 'Mulai Istirahat' : 'Mulai Fokus' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
