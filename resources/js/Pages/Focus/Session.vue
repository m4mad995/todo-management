<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    activeTask: Object,
});

const workTime = ref(25 * 60);
const breakTime = ref(5 * 60);
const timeLeft = ref(25 * 60);

const isRunning = ref(false);
const isBreak = ref(false);
const presetMode = ref('25/5');
const cyclesCompleted = ref(0);

let timerInterval = null;
let endTime = null;

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

const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

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
    endTime = null;
};

const resetTimer = () => {
    pauseTimer();
    timeLeft.value = isBreak.value ? breakTime.value : workTime.value;
};

const handlePhaseComplete = () => {
    pauseTimer();
    playBeep();

    if (!isBreak.value) {
        cyclesCompleted.value++;
        isBreak.value = true;
        timeLeft.value = breakTime.value;
        alert('Sesi kerja selesai! Saatnya istirahat.');
    } else {
        isBreak.value = false;
        timeLeft.value = workTime.value;
        alert('Istirahat selesai! Siap untuk fokus lagi?');
    }
};

const setPreset = (workMin, breakMin, modeLabel) => {
    pauseTimer();
    presetMode.value = modeLabel;
    workTime.value = workMin * 60;
    breakTime.value = breakMin * 60;
    isBreak.value = false;
    timeLeft.value = workTime.value;
};

const markAsComplete = () => {
    if (props.activeTask) {
        router.patch(`/tasks/${props.activeTask.id}`, {
            completed: true,
        }, {
            onSuccess: () => router.visit('/focus'),
        });
    }
};

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible' && isRunning.value) {
        updateRemainingTime();
    }
};

onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    clearInterval(timerInterval);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

const presets = [
    { work: 25, break: 5, label: '25/5', name: 'Standard' },
    { work: 50, break: 10, label: '50/10', name: 'Extended' },
    { work: 15, break: 3, label: '15/3', name: 'Sprint' },
];
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
        <div class="max-w-lg mx-auto card p-8 text-center">
            <!-- Mode Badge -->
            <span :class="isBreak ? 'badge-emerald' : 'badge-blue'" class="mb-4 inline-flex">
                {{ isBreak ? 'Istirahat' : 'Fokus' }}
            </span>

            <!-- Task Title -->
            <h1 class="text-xl font-bold text-gray-900 leading-snug mb-1">
                {{ activeTask ? activeTask.title : 'Focus Session' }}
            </h1>
            <p v-if="activeTask" class="text-gray-400 text-[13px] mb-6">
                Fokus penuh sampai timer habis.
            </p>
            <div v-else class="mb-6"></div>

            <!-- Preset Selector -->
            <div class="flex items-center justify-center gap-1 p-1 bg-gray-100 rounded-btn mb-8 w-fit mx-auto">
                <button
                    v-for="p in presets"
                    :key="p.label"
                    @click="setPreset(p.work, p.break, p.label)"
                    :class="[
                        'px-4 py-2 rounded-btn text-sm font-medium transition-all duration-150',
                        presetMode === p.label
                            ? 'bg-surface text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    {{ p.name }}
                </button>
            </div>

            <!-- Timer -->
            <div class="my-6">
                <div
                    class="text-6xl font-mono font-black tracking-tight transition-colors text-blue-600"
                >
                    {{ formattedTime }}
                </div>
            </div>

            <!-- Controls -->
            <div class="flex items-center justify-center gap-3 my-6">
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
</template>
