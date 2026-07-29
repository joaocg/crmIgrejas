<template>
    <AppShell :title="t('care.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('care.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('care.title') }}</h1>
            <p class="hero-panel__copy">{{ t('care.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('care.summary.notes') }}</template>
                    <template #content>
                        <strong>{{ stats.notes }}</strong>
                        <div class="app-brand__subtitle">{{ t('care.summary.notes_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('care.summary.records') }}</template>
                    <template #content>
                        <strong>{{ stats.records }}</strong>
                        <div class="app-brand__subtitle">{{ t('care.summary.records_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('care.summary.visibility') }}</template>
                    <template #content>
                        <strong>{{ t('care.summary.visibility_value') }}</strong>
                        <div class="app-brand__subtitle">{{ t('care.summary.visibility_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('care.notes.title') }}</template>
                <template #content>
                    <BaseDataTable :rows="noteRows" :rows-per-page="5">
                        <PColumn field="title" :header="t('care.notes.columns.title')" />
                        <PColumn field="person_name" :header="t('care.notes.columns.person')" />
                        <PColumn field="family_name" :header="t('care.notes.columns.family')" />
                        <PColumn field="private_label" :header="t('care.notes.columns.private')" />
                    </BaseDataTable>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('care.records.title') }}</template>
                <template #content>
                    <BaseDataTable :rows="recordRows" :rows-per-page="5">
                        <PColumn field="type" :header="t('care.records.columns.type')" />
                        <PColumn field="person_name" :header="t('care.records.columns.person')" />
                        <PColumn field="family_name" :header="t('care.records.columns.family')" />
                        <PColumn field="visible_label" :header="t('care.records.columns.visible')" />
                    </BaseDataTable>
                </template>
            </PCard>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { listNotes, listPastoralCareRecords } from '../../../api/modules/care';

const stats = reactive({
    notes: '0',
    records: '0',
});

const noteRows = reactive([]);
const recordRows = reactive([]);

onMounted(async () => {
    try {
        const [notesResponse, recordsResponse] = await Promise.all([
            listNotes(),
            listPastoralCareRecords(),
        ]);

        const notes = notesResponse.data?.data ?? notesResponse.data ?? [];
        const records = recordsResponse.data?.data ?? recordsResponse.data ?? [];

        noteRows.splice(0, noteRows.length, ...notes.map((note) => ({
            ...note,
            person_name: note.person ? `${note.person.first_name} ${note.person.last_name}` : '—',
            family_name: note.family?.name ?? '—',
            private_label: note.is_private ? t('forms.status.inactive') : t('forms.status.active'),
        })));

        recordRows.splice(0, recordRows.length, ...records.map((record) => ({
            ...record,
            person_name: record.person ? `${record.person.first_name} ${record.person.last_name}` : '—',
            family_name: record.family?.name ?? '—',
            visible_label: record.visible ? t('forms.status.active') : t('forms.status.inactive'),
        })));

        stats.notes = String(noteRows.length);
        stats.records = String(recordRows.length);
    } catch {
        noteRows.splice(0, noteRows.length, {
            title: t('care.fallback_note.title'),
            person_name: t('care.fallback_note.person'),
            family_name: t('care.fallback_note.family'),
            private_label: t('forms.status.inactive'),
        });
        recordRows.splice(0, recordRows.length, {
            type: t('care.fallback_record.type'),
            person_name: t('care.fallback_record.person'),
            family_name: t('care.fallback_record.family'),
            visible_label: t('forms.status.active'),
        });
    }
});
</script>
