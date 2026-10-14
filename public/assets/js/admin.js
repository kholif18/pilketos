document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    body.classList.add('admin-ready');

    const fullscreenButton = document.querySelector('[data-fullscreen-toggle]');
    if (fullscreenButton) {
        const iconExpand = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/><path d="M8 21H5a2 2 0 0 1-2-2v-3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
        const iconCompress = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>';

        const syncFullscreenState = function () {
            const presenting = document.fullscreenElement !== null;
            body.classList.toggle('is-presenting', presenting);
            fullscreenButton.innerHTML = presenting ? iconCompress : iconExpand;
            fullscreenButton.setAttribute('aria-label', presenting ? 'Keluar dari layar penuh' : 'Tampilkan layar penuh');
        };

        fullscreenButton.addEventListener('click', async function () {
            try {
                if (!document.fullscreenElement) {
                    await document.documentElement.requestFullscreen();
                    return;
                }

                await document.exitFullscreen();
            } catch (error) {
                console.warn('Fullscreen not supported in this browser.', error);
            }
        });

        document.addEventListener('fullscreenchange', syncFullscreenState);
        syncFullscreenState();
    }

    document.querySelectorAll('input[type="password"]').forEach(function (passwordInput) {
        const wrapper = passwordInput.parentElement;
        if (!wrapper || wrapper.querySelector('.password-toggle')) {
            return;
        }

        wrapper.classList.add('password-input-wrap');

        const eyeOpen = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3.25"/></svg>';
        const eyeSlash = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.5 2.5 0 0 0 13.4 13.4"/><path d="M9 5.5A12.8 12.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-4.1 5.4"/><path d="M5.9 7.6A17.5 17.5 0 0 0 2 12s3.5 7 10 7a11.7 11.7 0 0 0 5.1-1.3"/></svg>';

        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'password-toggle';
        toggleButton.setAttribute('aria-label', 'Tampilkan password');
        toggleButton.innerHTML = eyeOpen;

        const syncToggle = function () {
            const isHidden = passwordInput.type === 'password';
            toggleButton.innerHTML = isHidden ? eyeOpen : eyeSlash;
            toggleButton.setAttribute('aria-label', isHidden ? 'Tampilkan password' : 'Sembunyikan password');
        };

        toggleButton.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            syncToggle();
        });

        syncToggle();
        wrapper.appendChild(toggleButton);
    });

    document.querySelectorAll('[data-picker]').forEach(function (picker) {
        const fileInput = picker.querySelector('input[type="file"]');
        const label = picker.querySelector('.media-picker-name');
        const trigger = picker.querySelector('.media-picker-button');
        const previewBox = picker.querySelector('[data-preview]');
        const previewImg = previewBox ? previewBox.querySelector('img') : null;
        const tipeBoleh = ['image/jpeg', 'image/png', 'image/gif'];
        const maksUkuran = 1024 * 1024;

        const setelanAwal = function () {
            if (label) {
                label.textContent = 'Belum ada file dipilih';
            }
            if (previewImg) {
                previewImg.hidden = true;
                previewImg.removeAttribute('src');
            }
            if (previewBox) {
                previewBox.classList.add('empty');
            }
        };

        if (trigger && fileInput) {
            trigger.addEventListener('click', function () {
                fileInput.click();
            });
        }

        if (!fileInput) {
            return;
        }

        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0];
            picker.classList.remove('is-invalid');

            if (!file) {
                setelanAwal();
                return;
            }

            if (tipeBoleh.indexOf(file.type) === -1) {
                fileInput.value = '';
                setelanAwal();
                picker.classList.add('is-invalid');
                if (label) {
                    label.textContent = 'Format berkas harus JPG, PNG, atau GIF.';
                }
                return;
            }

            if (file.size > maksUkuran) {
                fileInput.value = '';
                setelanAwal();
                picker.classList.add('is-invalid');
                if (label) {
                    label.textContent = 'Ukuran berkas maksimal 1 MB.';
                }
                return;
            }

            if (label) {
                label.textContent = file.name + ' • ' + Math.max(1, Math.round(file.size / 1024)) + ' KB';
            }

            if (previewBox && previewImg) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    previewImg.src = event.target.result;
                    previewImg.hidden = false;
                    previewBox.classList.remove('empty');
                };
                reader.readAsDataURL(file);
            } else if (previewBox) {
                previewBox.classList.remove('empty');
            }
        });
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const submitter = event.submitter || form.querySelector('button[type="submit"]');
            const confirmMessage = (submitter && submitter.getAttribute('data-confirm')) || form.getAttribute('data-confirm');

            if (confirmMessage !== null && form.dataset.confirmed !== '1') {
                event.preventDefault();
                restoreSubmitButton(form);
                openSwal({
                    message: confirmMessage,
                    title: (submitter && submitter.getAttribute('data-confirm-title')) || form.getAttribute('data-confirm-title'),
                    okLabel: (submitter && submitter.getAttribute('data-confirm-ok')) || form.getAttribute('data-confirm-ok')
                }, function () {
                    form.dataset.confirmed = '1';
                    if (submitter && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                    } else {
                        form.submit();
                    }
                });
                return;
            }
            form.dataset.confirmed = '';

            const action = form.getAttribute('action') || '';
            const isLoginForm = action.includes('/login') || form.classList.contains('login-form');
            if (isLoginForm) {
                return;
            }

            const button = (submitter && submitter.type === 'submit') ? submitter : form.querySelector('button[type="submit"]');
            if (!button || button.disabled) {
                return;
            }

            if (!button.dataset.label) {
                button.dataset.label = button.textContent;
            }

            button.disabled = true;
            button.textContent = 'Menyimpan...';
        });
    });

    const restoreSubmitButton = function (form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            if (submitButton.dataset.label) {
                submitButton.textContent = submitButton.dataset.label;
            }
        }
    };

    let swalOverlay = null;
    let swalTitle = null;
    let swalMessage = null;
    let swalOk = null;
    let swalPendingOk = null;
    let swalLastFocus = null;

    const closeSwal = function () {
        if (!swalOverlay || swalOverlay.hidden) {
            return;
        }

        swalOverlay.classList.remove('is-open');
        swalPendingOk = null;
        window.setTimeout(function () {
            if (swalOverlay) {
                swalOverlay.hidden = true;
            }
        }, 180);

        if (swalLastFocus && typeof swalLastFocus.focus === 'function') {
            swalLastFocus.focus();
        }
    };

    const openSwal = function (options, onOk) {
        if (!swalOverlay) {
            swalOverlay = document.createElement('div');
            swalOverlay.className = 'swal-overlay';
            swalOverlay.hidden = true;
            swalOverlay.innerHTML = '<div class="swal-card" role="alertdialog" aria-modal="true" aria-labelledby="swal-title" aria-describedby="swal-message">'
                + '<span class="swal-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></span>'
                + '<h2 id="swal-title"></h2>'
                + '<p id="swal-message"></p>'
                + '<div class="swal-actions"><button type="button" class="btn btn-outline" data-swal-cancel>Batal</button>'
                + '<button type="button" class="btn btn-danger" data-swal-ok>Ya, Lanjutkan</button></div></div>';
            document.body.appendChild(swalOverlay);

            swalTitle = swalOverlay.querySelector('#swal-title');
            swalMessage = swalOverlay.querySelector('#swal-message');
            swalOk = swalOverlay.querySelector('[data-swal-ok]');

            swalOverlay.querySelector('[data-swal-cancel]').addEventListener('click', closeSwal);
            swalOverlay.addEventListener('click', function (event) {
                if (event.target === swalOverlay) {
                    closeSwal();
                }
            });
            swalOk.addEventListener('click', function () {
                const action = swalPendingOk;
                swalPendingOk = null;
                closeSwal();
                if (typeof action === 'function') {
                    action();
                }
            });
        }

        swalLastFocus = document.activeElement;
        swalTitle.textContent = options.title || 'Apakah Anda yakin?';
        swalMessage.textContent = options.message || '';
        swalOk.textContent = options.okLabel || 'Ya, Lanjutkan';
        swalPendingOk = onOk;
        swalOverlay.hidden = false;
        window.requestAnimationFrame(function () {
            if (swalOverlay) {
                swalOverlay.classList.add('is-open');
            }
        });
        swalOverlay.querySelector('[data-swal-cancel]').focus();
    };

    const dashShell = document.querySelector('[data-dash-shell]');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    if (dashShell && sidebarToggle) {
        const mobileQuery = window.matchMedia('(max-width: 1020px)');
        const storageKey = 'pilketos-sidebar-collapsed';

        const readCollapsed = function () {
            try {
                return window.localStorage.getItem(storageKey) === '1';
            } catch (error) {
                return false;
            }
        };

        const saveCollapsed = function (collapsed) {
            try {
                window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
            } catch (error) {
                console.warn('Gagal menyimpan preferensi sidebar.', error);
            }
        };

        const syncToggleLabel = function () {
            const expanded = mobileQuery.matches
                ? dashShell.classList.contains('sidebar-open')
                : !dashShell.classList.contains('sidebar-collapsed');
            sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        if (!mobileQuery.matches && readCollapsed()) {
            dashShell.classList.add('sidebar-collapsed');
        }
        syncToggleLabel();

        sidebarToggle.addEventListener('click', function () {
            if (mobileQuery.matches) {
                dashShell.classList.toggle('sidebar-open');
            } else {
                const collapsed = dashShell.classList.toggle('sidebar-collapsed');
                saveCollapsed(collapsed);
            }
            syncToggleLabel();
        });

        const closeMobileDrawer = function () {
            if (!mobileQuery.matches) {
                return;
            }
            dashShell.classList.remove('sidebar-open');
            syncToggleLabel();
        };

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMobileDrawer();
            }
        });

        dashShell.querySelectorAll('.dash-nav-link').forEach(function (link) {
            link.addEventListener('click', closeMobileDrawer);
        });

        const sidebarBackdrop = dashShell.querySelector('[data-sidebar-backdrop]');
        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', closeMobileDrawer);
        }

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', function () {
                dashShell.classList.remove('sidebar-open');
                syncToggleLabel();
            });
        }
    }

    const searchInput = document.querySelector('[data-candidate-search]');
    const searchDrop = document.querySelector('[data-search-drop]');
    const candidateCards = Array.from(document.querySelectorAll('[data-candidate-card]'));

    const MENU_CARI = [
        { label: 'Beranda', href: '/admin', info: 'Dashboard' },
        { label: 'Kandidat', href: '/admin/calon', info: 'Kelola calon' },
        { label: 'Pemilih', href: '/admin/siswa', info: 'Data siswa' },
        { label: 'Kelas', href: '/admin/kelas', info: 'Data kelas' },
        { label: 'Hasil', href: '/admin/hasilvote', info: 'Hasil voting' },
        { label: 'Daftar Hadir', href: '/admin/daftarhadir', info: 'Kehadiran' },
        { label: 'Laporan', href: '/admin/laporan', info: 'Laporan' },
        { label: 'Identitas', href: '/admin/identitas', info: 'Sekolah' },
        { label: 'Koneksi API', href: '/admin/connect-api', info: 'API sekolah' },
        { label: 'Pengaturan', href: '/admin/pengaturan', info: 'Akun' },
    ];

    let kandidatCache = null;

    const kandidatHalamanIni = function () {
        return candidateCards.map(function (card) {
            return {
                nama: card.getAttribute('data-nama') || '',
                no: card.getAttribute('data-no') || '',
            };
        }).filter(function (item) {
            return item.nama !== '';
        });
    };

    const muatKandidatGlobal = function (lalu) {
        if (kandidatCache !== null) {
            lalu(kandidatCache);
            return;
        }

        fetch('/admin/hasilvote/data', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('gagal');
                }
                return res.json();
            })
            .then(function (data) {
                kandidatCache = (data && data.ok === true && Array.isArray(data.hasil)) ? data.hasil : [];
                lalu(kandidatCache);
            })
            .catch(function () {
                kandidatCache = [];
                lalu(kandidatCache);
            });
    };

    const tutupDrop = function () {
        if (!searchDrop) {
            return;
        }
        searchDrop.hidden = true;
        if (searchInput) {
            searchInput.setAttribute('aria-expanded', 'false');
        }
    };

    const gambarDrop = function (menu, kandidat) {
        if (!searchDrop || !searchInput) {
            return;
        }

        searchDrop.innerHTML = '';

        if (menu.length === 0 && kandidat.length === 0) {
            const kosong = document.createElement('p');
            kosong.className = 'search-empty';
            kosong.textContent = 'Tidak ada hasil untuk "' + searchInput.value.trim() + '".';
            searchDrop.appendChild(kosong);
            searchDrop.hidden = false;
            searchInput.setAttribute('aria-expanded', 'true');
            return;
        }

        const tambahGrup = function (judul, daftar, jenis) {
            if (daftar.length === 0) {
                return;
            }

            const label = document.createElement('span');
            label.className = 'search-group-label';
            label.textContent = judul;
            searchDrop.appendChild(label);

            daftar.forEach(function (item) {
                const tombol = document.createElement('button');
                tombol.type = 'button';
                tombol.className = 'search-item';
                tombol.setAttribute('role', 'option');

                const nama = document.createElement('span');
                nama.textContent = item.label;
                tombol.appendChild(nama);

                const info = document.createElement('small');
                info.textContent = item.info;
                tombol.appendChild(info);

                tombol.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    window.location.href = item.href;
                });
                tombol.addEventListener('click', function () {
                    window.location.href = item.href;
                });

                searchDrop.appendChild(tombol);
            });
        };

        tambahGrup('Menu', menu.map(function (item) {
            return { label: item.label, info: item.info, href: item.href };
        }));
        tambahGrup('Kandidat', kandidat.map(function (item) {
            return { label: item.nama, info: 'No. ' + item.no, href: '/admin/calon' };
        }));

        searchDrop.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
    };

    const sorotAktif = function (arah) {
        if (!searchDrop || searchDrop.hidden) {
            return;
        }

        const daftar = Array.from(searchDrop.querySelectorAll('.search-item'));
        if (daftar.length === 0) {
            return;
        }

        let posisi = daftar.findIndex(function (el) {
            return el.classList.contains('is-active');
        });
        posisi = arah === 'atas' ? posisi - 1 : posisi + 1;
        if (posisi < 0) {
            posisi = daftar.length - 1;
        }
        if (posisi >= daftar.length) {
            posisi = 0;
        }

        daftar.forEach(function (el) {
            el.classList.remove('is-active');
        });
        daftar[posisi].classList.add('is-active');
        daftar[posisi].scrollIntoView({ block: 'nearest' });
    };

    const cariGlobal = function () {
        if (!searchInput) {
            return;
        }

        const keyword = searchInput.value.toLowerCase().trim();
        if (keyword === '') {
            tutupDrop();
            return;
        }

        const menuCocok = MENU_CARI.filter(function (item) {
            return item.label.toLowerCase().includes(keyword);
        });

        const diHalaman = kandidatHalamanIni();
        if (diHalaman.length > 0) {
            gambarDrop(menuCocok, diHalaman.filter(function (item) {
                return item.nama.toLowerCase().includes(keyword);
            }));
            return;
        }

        muatKandidatGlobal(function (daftar) {
            if (searchInput.value.toLowerCase().trim() !== keyword) {
                return;
            }
            gambarDrop(menuCocok, daftar.filter(function (item) {
                return String(item.nama || '').toLowerCase().includes(keyword);
            }).map(function (item) {
                return { nama: item.nama, no: item.no };
            }));
        });
    };

    if (searchInput && candidateCards.length > 0) {
        const countLabel = document.querySelector('[data-candidate-count]');
        const emptyState = document.querySelector('[data-candidate-empty]');
        const totalLabel = countLabel ? countLabel.textContent : '';

        searchInput.addEventListener('input', function () {
            const keyword = searchInput.value.toLowerCase().trim();
            let visible = 0;

            candidateCards.forEach(function (card) {
                const haystack = (card.getAttribute('data-search') || '').toLowerCase();
                const match = keyword === '' || haystack.includes(keyword);
                card.hidden = !match;
                if (match) {
                    visible += 1;
                }
            });

            if (countLabel) {
                countLabel.textContent = keyword === '' ? totalLabel : visible + ' kandidat ditemukan';
            }

            if (emptyState) {
                emptyState.hidden = visible !== 0;
            }

            cariGlobal();
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                sorotAktif(event.key === 'ArrowUp' ? 'atas' : 'bawah');
            } else if (event.key === 'Enter') {
                const aktif = searchDrop ? searchDrop.querySelector('.search-item.is-active') : null;
                const pertama = searchDrop ? searchDrop.querySelector('.search-item') : null;
                if (aktif || pertama) {
                    event.preventDefault();
                    (aktif || pertama).click();
                }
            } else if (event.key === 'Escape') {
                tutupDrop();
            }
        });

        document.addEventListener('click', function (event) {
            if (searchDrop && !searchDrop.hidden && !event.target.closest('.dash-search-wrap')) {
                tutupDrop();
            }
        });
    } else if (searchInput) {
        searchInput.addEventListener('input', cariGlobal);

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                sorotAktif(event.key === 'ArrowUp' ? 'atas' : 'bawah');
            } else if (event.key === 'Enter') {
                const aktif = searchDrop ? searchDrop.querySelector('.search-item.is-active') : null;
                const pertama = searchDrop ? searchDrop.querySelector('.search-item') : null;
                if (aktif || pertama) {
                    event.preventDefault();
                    (aktif || pertama).click();
                }
            } else if (event.key === 'Escape') {
                tutupDrop();
            }
        });

        document.addEventListener('click', function (event) {
            if (searchDrop && !searchDrop.hidden && !event.target.closest('.dash-search-wrap')) {
                tutupDrop();
            }
        });
    }

    const liveGrid = document.querySelector('[data-live-grid]');
    if (liveGrid) {
        const fmtPersenID = function (suara, total) {
            if (!total) {
                return '0,0%';
            }
            return (Math.round(suara / total * 1000) / 10).toFixed(1).replace('.', ',') + '%';
        };

        const fmtLebar = function (suara, total) {
            if (!total) {
                return '0.0';
            }
            return (Math.min(100, Math.max(0, Math.round(suara / total * 1000) / 10))).toFixed(1);
        };

        const terapkanBaris = function (kartu, suara, total, unggul) {
            const angka = kartu.querySelector('[data-suara]');
            if (angka) {
                angka.textContent = suara;
            }

            const bar = kartu.querySelector('[data-bar]');
            if (bar) {
                bar.style.width = fmtLebar(suara, total) + '%';
            }

            const caption = kartu.querySelector('[data-persen]');
            if (caption) {
                caption.textContent = fmtPersenID(suara, total) + ' dari total suara masuk';
            }

            const kepala = kartu.querySelector('[data-kepala]');
            const lencana = kepala ? kepala.querySelector('.public-leader') : null;
            if (unggul && kepala && !lencana) {
                const baru = document.createElement('span');
                baru.className = 'public-leader';
                baru.textContent = 'Unggul';
                kepala.appendChild(baru);
            } else if (!unggul && lencana) {
                lencana.remove();
            }

            kartu.classList.toggle('winner', unggul);
        };

        const muatLive = function () {
            if (document.hidden) {
                return;
            }

            fetch('/admin/hasilvote/data', {headers: {'Accept': 'application/json'}, credentials: 'same-origin'})
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('gagal');
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || data.ok !== true || !Array.isArray(data.hasil)) {
                        return;
                    }

                    const peta = {};
                    data.hasil.forEach(function (item) {
                        peta[String(item.no)] = item;
                    });

                    const kartuList = Array.from(liveGrid.querySelectorAll('[data-calon-no]'));
                    if (kartuList.length !== data.hasil.length) {
                        window.location.reload();
                        return;
                    }

                    let maks = 0;
                    data.hasil.forEach(function (item) {
                        maks = Math.max(maks, Number(item.jumlah) || 0);
                    });

                    const setel = function (kunci, nilai) {
                        const el = document.querySelector('[data-live="' + kunci + '"]');
                        if (el) {
                            el.textContent = nilai;
                        }
                    };
                    setel('pemilih', data.pemilih);
                    setel('sudah', data.sudah);
                    setel('belum', data.belum);

                    kartuList.forEach(function (kartu) {
                        const item = peta[kartu.getAttribute('data-calon-no')];
                        if (!item) {
                            return;
                        }
                        const suara = Number(item.jumlah) || 0;
                        terapkanBaris(kartu, suara, data.sudah, maks > 0 && suara === maks);
                    });

                    const catatan = document.querySelector('[data-live-note]');
                    if (catatan && data.diperbarui) {
                        catatan.textContent = 'Diperbarui otomatis • ' + data.diperbarui;
                    }
                })
                .catch(function () {
                });
        };

        window.setInterval(muatLive, 10000);
    }

    const editModal = document.querySelector('[data-edit-modal]');
    if (editModal) {
        const editId = editModal.querySelector('[data-edit-id]');
        const editNama = editModal.querySelector('[data-edit-nama]');

        const tutupEditModal = function () {
            editModal.classList.remove('is-open');
            window.setTimeout(function () {
                editModal.hidden = true;
            }, 180);
        };

        document.querySelectorAll('[data-edit-kelas]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                if (editId) {
                    editId.value = tombol.getAttribute('data-id') || '';
                }
                if (editNama) {
                    editNama.value = tombol.getAttribute('data-nama') || '';
                }
                editModal.hidden = false;
                window.requestAnimationFrame(function () {
                    editModal.classList.add('is-open');
                });
                if (editNama) {
                    editNama.focus();
                }
            });
        });

        const editBatal = editModal.querySelector('[data-edit-batal]');
        if (editBatal) {
            editBatal.addEventListener('click', tutupEditModal);
        }

        editModal.addEventListener('click', function (event) {
            if (event.target === editModal) {
                tutupEditModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !editModal.hidden) {
                tutupEditModal();
            }
        });
    }

    document.querySelectorAll('input[type="file"][data-image-input]').forEach(function (fileInput) {
        const tipeBoleh = ['image/jpeg', 'image/png', 'image/gif'];
        const maksUkuran = 1024 * 1024;

        fileInput.addEventListener('change', function () {
            const field = fileInput.closest('.field');
            const pesan = field ? field.querySelector('[data-file-error]') : null;
            const file = fileInput.files && fileInput.files[0];

            if (pesan) {
                pesan.hidden = true;
                pesan.textContent = '';
            }
            if (!file) {
                return;
            }

            let galat = '';
            if (tipeBoleh.indexOf(file.type) === -1) {
                galat = 'Format berkas harus JPG, PNG, atau GIF.';
            } else if (file.size > maksUkuran) {
                galat = 'Ukuran berkas maksimal 1 MB.';
            }

            if (galat !== '') {
                fileInput.value = '';
                if (pesan) {
                    pesan.textContent = galat;
                    pesan.hidden = false;
                }
            }
        });
    });

    document.querySelectorAll('a[data-confirm]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const href = link.getAttribute('href');
            openSwal({
                message: link.getAttribute('data-confirm') || '',
                title: link.getAttribute('data-confirm-title'),
                okLabel: link.getAttribute('data-confirm-ok')
            }, function () {
                window.location.href = href;
            });
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSwal();
        }
    });
});
