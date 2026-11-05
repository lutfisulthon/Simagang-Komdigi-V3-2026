/*
 * Workspace — label tim.
 * Dibuka lewat event window ws-open-labels { teamId }; perubahan disiarkan lewat
 * ws-labels-changed { teamId, labels }. Dipakai partial workspace/partials/label-manager.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const emit = (name, detail) => window.dispatchEvent(new CustomEvent(name, { detail }));

    const LABEL_COLORS = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#ef4444', '#8b5cf6', '#64748b'];

    // Perbarui daftar label milik sebuah item (kartu tugas, template) setelah label tim berubah:
    // nama/warna mengikuti data baru, label yang dihapus dibuang.
    function applyLabels(itemLabels, labels) {
        const byId = new Map(labels.map((l) => [l.id, l]));
        return (itemLabels || []).filter((l) => byId.has(l.id)).map((l) => Object.assign({}, byId.get(l.id)));
    }

    WS.labels = { COLORS: LABEL_COLORS, apply: applyLabels };


    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // config: { teams: {id,name}[], labelsByTeam: { [teamId]: Label[] } }
        Alpine.data('wsLabelManager', (config) => ({
            open: false,
            teams: config.teams,
            labelsByTeam: config.labelsByTeam,
            teamId: null,
            draft: { name: '', color: LABEL_COLORS[0] },
            editingId: null,
            edit: { name: '', color: '' },
            confirmId: null,
            errors: {},
            busy: false,
            colors: LABEL_COLORS,

            init() {
                window.addEventListener('ws-open-labels', (e) => {
                    this.teamId = (e.detail && e.detail.teamId) || (this.teams[0] && this.teams[0].id);
                    this.reset();
                    this.open = true;
                });
            },

            get labels() {
                return this.labelsByTeam[this.teamId] || [];
            },

            get teamName() {
                const t = this.teams.find((x) => x.id === Number(this.teamId));
                return t ? t.name : '';
            },

            reset() {
                this.draft = { name: '', color: LABEL_COLORS[this.labels.length % LABEL_COLORS.length] };
                this.editingId = null;
                this.confirmId = null;
                this.errors = {};
            },

            check(name, exceptId) {
                const n = name.trim();
                if (!n) return 'Nama label wajib diisi.';
                if (n.length > 30) return 'Maksimal 30 karakter.';
                if (this.labels.some((l) => l.id !== exceptId && l.name.toLowerCase() === n.toLowerCase())) return 'Label dengan nama ini sudah ada.';
                return '';
            },

            publish(list) {
                this.labelsByTeam[this.teamId] = list;
                emit('ws-labels-changed', { teamId: Number(this.teamId), labels: JSON.parse(JSON.stringify(list)) });
            },

            async add() {
                const error = this.check(this.draft.name);
                this.errors = error ? { name: error } : {};
                if (error) return;
                this.busy = true;
                try {
                    const res = await WS.api.labels.create(Number(this.teamId), { name: this.draft.name.trim(), color: this.draft.color });
                    this.publish(this.labels.concat(res.data));
                    WS.toast(`Label "${res.data.name}" ditambahkan.`, 'success');
                    this.reset();
                    this.$nextTick(() => this.$root.querySelector('[data-label-new]')?.focus());
                } catch (err) {
                    if (err.status === 422) this.errors = { name: (err.errors.name || [err.message])[0] };
                    else WS.toast(err.message, 'error');
                } finally {
                    this.busy = false;
                }
            },

            startEdit(l) {
                this.editingId = l.id;
                this.confirmId = null;
                this.edit = { name: l.name, color: l.color };
                this.errors = {};
                this.$nextTick(() => this.$root.querySelector('[data-label-edit]')?.focus());
            },

            async saveEdit(l) {
                const error = this.check(this.edit.name, l.id);
                this.errors = error ? { edit: error } : {};
                if (error) return;
                this.busy = true;
                try {
                    const res = await WS.api.labels.update(l.id, { name: this.edit.name.trim(), color: this.edit.color });
                    this.publish(this.labels.map((x) => (x.id === l.id ? res.data : x)));
                    this.editingId = null;
                } catch (err) {
                    if (err.status === 422) this.errors = { edit: (err.errors.name || [err.message])[0] };
                    else WS.toast(err.message, 'error');
                } finally {
                    this.busy = false;
                }
            },

            async remove(l) {
                if (this.confirmId !== l.id) { this.confirmId = l.id; return; }
                this.busy = true;
                try {
                    await WS.api.labels.remove(l.id);
                    this.publish(this.labels.filter((x) => x.id !== l.id));
                    WS.toast(`Label "${l.name}" dihapus dan dilepas dari semua tugas.`, 'success');
                    this.confirmId = null;
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.busy = false;
                }
            },
        }));
    });
})();
