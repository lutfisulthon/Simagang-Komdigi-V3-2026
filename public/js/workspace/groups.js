/*
 * Workspace — kelompok buatan mentor (buat, tambah anggota, ganti nama, tandai selesai).
 * Markup: resources/views/workspace/partials/group-modals.blade.php (satu kali per halaman).
 *
 * Dibuka lewat event window (bisa dari halaman mana pun yang memuat partial-nya):
 *   ws-group-create { intern_ids?: [internId] }   buat kelompok baru (anggota bisa sudah dicentang)
 *   ws-group-assign { intern }                    masukkan satu anak magang: ke kelompok lama atau baru
 *   ws-group-add    { group }                     tambah anggota ke kelompok
 *   ws-group-rename { group }                     ganti nama & warna
 *   ws-group-finish { group }                     tandai kelompok selesai magang
 * Aturan: 1 anak magang = 1 kelompok, anggota tidak bisa dikeluarkan. Server tetap memvalidasi.
 */
(function () {
    'use strict';

    const WS = window.WS;

    // Warna kelompok (kontras teks putih ≥ 4,5:1).
    const COLORS = ['#4f46e5', '#2563eb', '#0f766e', '#15803d', '#a16207', '#c2410c', '#be185d', '#7c3aed'];

    function initials(name) {
        return WS.initials(name);
    }

    document.addEventListener('alpine:init', () => {
        // Halaman Anak Bimbingan: kolom "Kelompok" + centang untuk membuat kelompok.
        // Baris tabel adalah data asli (id intern DB); kelompoknya dari WS.api.groups.roster().
        window.Alpine.data('wsInternGroups', () => ({
            byId: {},
            loaded: false,
            selected: [],

            async init() {
                try {
                    const res = await WS.api.groups.roster();
                    res.interns.forEach((i) => { this.byId[i.id] = i; });
                } catch (_) { /* kolom kelompok tetap kosong */ }
                this.loaded = true;
            },

            group(id) {
                return this.byId[id] ? this.byId[id].group : null;
            },

            // Workspace Tim/Bagian (otomatis dari data Tim), hanya ditampilkan.
            team(id) {
                return this.byId[id] ? this.byId[id].team || null : null;
            },

            // Hanya anak bimbingan aktif yang belum punya kelompok yang bisa dicentang.
            selectable(id) {
                return !!this.byId[id] && !this.byId[id].group;
            },

            toggle(id) {
                const i = this.selected.indexOf(id);
                if (i === -1) this.selected.push(id);
                else this.selected.splice(i, 1);
            },

            create() {
                window.dispatchEvent(new CustomEvent('ws-group-create', { detail: { intern_ids: this.selected } }));
            },
        }));

        window.Alpine.data('wsGroupDialogs', () => ({
            colors: COLORS,
            roster: null,          // { interns, groups } — dimuat saat dialog pertama dibuka
            loading: false,
            loadError: '',
            saving: false,
            errors: {},
            q: '',

            createOpen: false,
            name: '',
            color: COLORS[0],
            description: '',
            selected: [],

            assignOpen: false,
            intern: null,
            target: '',            // id kelompok tujuan, atau 'new'

            addOpen: false,
            renameOpen: false,
            finishOpen: false,
            group: null,

            init() {
                this.$watch('name', () => { if (this.errors.name) this.errors = Object.assign({}, this.errors, { name: '' }); });
                window.addEventListener('ws-group-create', (e) => this.openCreate((e.detail && e.detail.intern_ids) || []));
                window.addEventListener('ws-group-assign', (e) => this.openAssign(e.detail.intern));
                window.addEventListener('ws-group-add', (e) => this.openAdd(e.detail.group));
                window.addEventListener('ws-group-rename', (e) => this.openRename(e.detail.group));
                window.addEventListener('ws-group-finish', (e) => this.openFinish(e.detail.group));
            },

            async loadRoster() {
                this.loading = true;
                this.loadError = '';
                try {
                    this.roster = await WS.api.groups.roster();
                } catch (err) {
                    this.loadError = err.message;
                } finally {
                    this.loading = false;
                }
            },

            reset() {
                this.errors = {};
                this.q = '';
                this.saving = false;
            },

            // ---------- Daftar anak bimbingan ----------

            get interns() {
                const list = this.roster ? this.roster.interns : [];
                const q = this.q.trim().toLowerCase();
                return q ? list.filter((i) => i.name.toLowerCase().includes(q)) : list;
            },

            get free() {
                return this.roster ? this.roster.interns.filter((i) => !i.group) : [];
            },

            toggle(id) {
                const i = this.selected.indexOf(id);
                if (i === -1) this.selected.push(id);
                else this.selected.splice(i, 1);
                if (this.errors.intern_ids) this.errors = Object.assign({}, this.errors, { intern_ids: '' });
            },

            initials,

            // ---------- Buat kelompok ----------

            openCreate(ids) {
                this.reset();
                this.name = '';
                this.color = COLORS[0];
                this.description = '';
                this.selected = ids.map(Number);
                this.assignOpen = false;
                this.createOpen = true;
                if (!this.roster) this.loadRoster();
            },

            async create() {
                const errors = {};
                if (!this.name.trim()) errors.name = 'Nama kelompok wajib diisi.';
                else if (this.name.trim().length > 60) errors.name = 'Nama kelompok maksimal 60 karakter.';
                if (!this.selected.length) errors.intern_ids = 'Pilih minimal satu anak magang.';
                this.errors = errors;
                if (Object.keys(errors).length) return;
                this.saving = true;
                try {
                    const res = await WS.api.groups.create({
                        name: this.name.trim(), color: this.color, description: this.description.trim(), intern_ids: this.selected,
                    });
                    WS.toast(`Kelompok "${res.data.name}" dibuat. Membuka workspace kelompok…`, 'success');
                    setTimeout(() => { window.location.href = res.data.url; }, 700);
                } catch (err) {
                    this.fail(err);
                }
            },

            // ---------- Masukkan satu anak magang ----------

            openAssign(intern) {
                this.reset();
                this.intern = intern;
                this.target = '';
                this.assignOpen = true;
                if (!this.roster) this.loadRoster();
            },

            async assign() {
                if (!this.target) { this.errors = { target: 'Pilih kelompok tujuan.' }; return; }
                if (this.target === 'new') { this.openCreate([this.intern.id]); return; }
                this.saving = true;
                try {
                    const res = await WS.api.groups.addMembers(Number(this.target), { intern_ids: [this.intern.id] });
                    WS.toast(`${this.intern.name} dimasukkan ke kelompok "${res.data.name}".`, 'success');
                    setTimeout(() => window.location.reload(), 700);
                } catch (err) {
                    this.fail(err);
                }
            },

            // ---------- Tambah anggota ----------

            openAdd(group) {
                this.reset();
                this.group = group;
                this.selected = [];
                this.addOpen = true;
                if (!this.roster) this.loadRoster();
            },

            async add() {
                if (!this.selected.length) { this.errors = { intern_ids: 'Pilih minimal satu anak magang.' }; return; }
                this.saving = true;
                try {
                    await WS.api.groups.addMembers(this.group.id, { intern_ids: this.selected });
                    WS.toast(`${this.selected.length} anak magang ditambahkan ke kelompok.`, 'success');
                    setTimeout(() => window.location.reload(), 700);
                } catch (err) {
                    this.fail(err);
                }
            },

            // ---------- Ganti nama ----------

            openRename(group) {
                this.reset();
                this.group = group;
                this.name = group.name;
                this.color = group.color || COLORS[0];
                this.renameOpen = true;
            },

            async rename() {
                const name = this.name.trim();
                if (!name) { this.errors = { name: 'Nama kelompok wajib diisi.' }; return; }
                if (name.length > 60) { this.errors = { name: 'Nama kelompok maksimal 60 karakter.' }; return; }
                this.saving = true;
                try {
                    await WS.api.groups.update(this.group.id, { name, color: this.color });
                    WS.toast('Nama kelompok disimpan.', 'success');
                    setTimeout(() => window.location.reload(), 600);
                } catch (err) {
                    this.fail(err);
                }
            },

            // ---------- Tandai selesai ----------

            openFinish(group) {
                this.reset();
                this.group = group;
                this.finishOpen = true;
            },

            async finish() {
                this.saving = true;
                try {
                    await WS.api.groups.update(this.group.id, { status: 'finished' });
                    WS.toast('Kelompok ditandai selesai magang.', 'success');
                    setTimeout(() => window.location.reload(), 600);
                } catch (err) {
                    this.fail(err);
                }
            },

            fail(err) {
                this.saving = false;
                if (err.status === 422 && err.errors) {
                    this.errors = Object.fromEntries(Object.entries(err.errors).map(([k, v]) => [k, v[0]]));
                } else {
                    WS.toast(err.message, 'error');
                }
            },
        }));
    });
})();
