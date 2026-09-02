<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    agendas: { type: Array, default: () => [] }
});

const isModalOpen = ref(false);
const editingAgenda = ref(null);

const form = useForm({
    title: '',
    notes: '',
    event_date: '',
    event_time: '',
});

const todayStr = new Date().toISOString().split('T')[0];

const todayAgendas = computed(() => {
    return props.agendas.filter(a => a.event_date === todayStr && !a.is_completed);
});

const upcomingAgendas = computed(() => {
    return props.agendas.filter(a => a.event_date > todayStr && !a.is_completed);
});

const completedOrPastAgendas = computed(() => {
    return props.agendas.filter(a => a.is_completed || a.event_date < todayStr);
});

const openModal = (agenda = null) => {
    if (agenda) {
        editingAgenda.value = agenda;
        form.title = agenda.title;
        form.notes = agenda.notes || '';
        form.event_date = agenda.event_date;
        form.event_time = agenda.event_time || '';
    } else {
        editingAgenda.value = null;
        form.reset();
        form.event_date = todayStr;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (editingAgenda.value) {
        form.patch(`/agenda/${editingAgenda.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/agenda', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleComplete = (agenda) => {
    router.patch(`/agenda/${agenda.id}`, {
        is_completed: !agenda.is_completed,
    });
};

const deleteAgenda = (id) => {
    router.delete(`/agenda/${id}`);
};

const formatTime = (time) => {
    return time ? time.substring(0, 5) : null;
};
</script>

<template>
    <Head title="Agenda" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Agenda & Pengingat</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-0.5 text-[15px]">
                    Catat jadwal dan batas waktu pentingmu.
                </p>
            </div>

            <button @click="openModal()" class="btn-primary btn-md self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Agenda Baru
            </button>
        </div>

        <div class="space-y-6">
            <!-- Today -->
            <section v-if="todayAgendas.length > 0">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    <span class="section-title">Hari Ini</span>
                    <span class="badge-red">{{ todayAgendas.length }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        v-for="item in todayAgendas"
                        :key="item.id"
                        class="card bg-red-50/30 dark:bg-red-900/10 border-red-200 dark:border-red-800 p-4 flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <span class="badge-red text-[13px]">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ formatTime(item.event_time) || 'Sepanjang Hari' }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <button @click="openModal(item)" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 text-xs font-medium px-1.5 py-0.5 transition">
                                        Edit
                                    </button>
                                    <button @click="deleteAgenda(item.id)" class="text-gray-400 dark:text-gray-500 hover:text-red-500 text-xs font-medium px-1.5 py-0.5 transition">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <h3 class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 mb-1">{{ item.title }}</h3>
                            <p v-if="item.notes" class="text-[13px] text-gray-600 dark:text-gray-400 whitespace-pre-line bg-white/60 dark:bg-slate-800/60 p-2.5 rounded-btn border border-red-100">
                                {{ item.notes }}
                            </p>
                        </div>

                        <button
                            @click="toggleComplete(item)"
                            class="mt-3 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-1.5 rounded-btn transition"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </section>

            <!-- Upcoming -->
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <span class="section-title">Akan Datang</span>
                    <span class="badge-blue">{{ upcomingAgendas.length }}</span>
                </div>

                <div v-if="upcomingAgendas.length === 0" class="card p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada agenda mendatang.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    <div
                        v-for="item in upcomingAgendas"
                        :key="item.id"
                        class="card-hover p-4 flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <span class="badge-blue text-[13px]">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ item.event_date }}
                                    <span v-if="item.event_time" class="ml-1">{{ formatTime(item.event_time) }}</span>
                                </span>
                                <div class="flex items-center gap-1">
                                    <button @click="openModal(item)" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 text-[11px] font-medium px-1.5 py-0.5 transition">
                                        Edit
                                    </button>
                                    <button @click="deleteAgenda(item.id)" class="text-gray-400 dark:text-gray-500 hover:text-red-500 text-[11px] font-medium px-1.5 py-0.5 transition">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <h3 class="font-semibold text-[15px] text-gray-800 dark:text-gray-200 mb-1">{{ item.title }}</h3>
                            <p v-if="item.notes" class="text-[13px] text-gray-500 dark:text-gray-400 whitespace-pre-line bg-gray-50 dark:bg-slate-800 p-2.5 rounded-btn">
                                {{ item.notes }}
                            </p>
                        </div>

                        <button
                            @click="toggleComplete(item)"
                            class="mt-3 w-full btn-outline text-xs font-semibold py-1.5"
                        >
                            Tandai Selesai
                        </button>
                    </div>
                </div>
            </section>

            <!-- Completed / Past -->
            <section v-if="completedOrPastAgendas.length > 0">
                <details class="group">
                    <summary class="cursor-pointer text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1.5 select-none transition">
                        <svg class="w-3.5 h-3.5 transition group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        Selesai & Lewat ({{ completedOrPastAgendas.length }})
                    </summary>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 mt-3">
                        <div
                            v-for="item in completedOrPastAgendas"
                            :key="item.id"
                            class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-card p-3 flex flex-col justify-between opacity-60"
                        >
                            <div>
                                <div class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500 mb-1">
                                    <span>{{ item.event_date }}</span>
                                    <button @click="deleteAgenda(item.id)" class="hover:text-red-600 font-semibold">Hapus</button>
                                </div>
                                <h4 class="font-semibold text-[13px] text-gray-500 dark:text-gray-400 line-through">{{ item.title }}</h4>
                            </div>
                            <button
                                @click="toggleComplete(item)"
                                class="mt-2 text-xs text-blue-600 hover:underline font-semibold text-left"
                            >
                                Kembalikan
                            </button>
                        </div>
                    </div>
                </details>
            </section>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="closeModal"></div>

                    <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-md animate-slide-up">
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-5">
                                {{ editingAgenda ? 'Edit Agenda' : 'Agenda Baru' }}
                            </h3>

                            <form @submit.prevent="submitForm" class="space-y-4">
                                <div>
                                    <label class="label">Nama Agenda</label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Contoh: Rapat Proyek, Ujian Semester"
                                        required
                                        class="input"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="label">Tanggal</label>
                                        <input v-model="form.event_date" type="date" required class="input" />
                                    </div>
                                    <div>
                                        <label class="label">Jam (Opsional)</label>
                                        <input v-model="form.event_time" type="time" class="input" />
                                    </div>
                                </div>

                                <div>
                                    <label class="label">Catatan / Persiapan</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="Bawa berkas, pakaian kemeja..."
                                        class="input resize-none"
                                    ></textarea>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="closeModal" class="btn-ghost btn-sm">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="form.processing" class="btn-primary btn-sm">
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
