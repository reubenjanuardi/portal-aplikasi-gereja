<div
    class="reset-pwd-flow"
    x-data="{
        generated: false,
        loading: false,
        copied: false,
        copyError: false,
        errorMsg: '',
        resetUrl: '',
        expireMinutes: {{ $expireMinutes }},

        async generateLink() {
            this.loading = true;
            this.errorMsg = '';
            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
                    || '{{ csrf_token() }}';

                const response = await fetch('{{ route('admin.users.generate-reset-link', $record) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Gagal membuat link reset password.');
                }

                const data = await response.json();
                this.resetUrl = data.url;
                this.expireMinutes = data.expireMinutes || {{ $expireMinutes }};
                this.generated = true;
            } catch (err) {
                this.errorMsg = err.message || 'Terjadi kesalahan pada sistem.';
            } finally {
                this.loading = false;
            }
        },

        copyToClipboard() {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(this.resetUrl)
                    .then(() => {
                        this.copied = true;
                        this.copyError = false;
                        setTimeout(() => this.copied = false, 3500);
                    })
                    .catch(() => {
                        this.fallbackCopy();
                    });
            } else {
                this.fallbackCopy();
            }
        },

        fallbackCopy() {
            const input = document.getElementById('generated-reset-password-url-field-{{ $record->id }}');
            if (input) {
                input.focus();
                input.select();
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        this.copied = true;
                        this.copyError = false;
                        setTimeout(() => this.copied = false, 3500);
                        return;
                    }
                } catch (e) {}
            }
            this.copyError = true;
        },

        closeModal(el) {
            const modal = el ? (el.closest('.fi-modal') || el.closest('[data-fi-modal-id]') || el.closest('[role=dialog]')) : document.querySelector('.fi-modal, [data-fi-modal-id]');
            const closeBtn = modal ? modal.querySelector('.fi-modal-close-btn') : document.querySelector('.fi-modal-close-btn');
            if (closeBtn) {
                closeBtn.click();
                return;
            }

            const modalId = modal?.getAttribute('data-fi-modal-id') || modal?.id;
            if (modalId && typeof this.$dispatch === 'function') {
                this.$dispatch('close-modal', { id: modalId });
            }
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('close-modal');
            }

            if (typeof this.$wire !== 'undefined' && typeof this.$wire.unmountAction === 'function') {
                this.$wire.unmountAction();
            }
        }
    }"
>
    <!-- Scoped CSS Stylesheet -->
    <style>
        .reset-pwd-flow {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            text-align: left;
            color: #1e293b;
            line-height: 1.5;
        }
        .reset-pwd-flow * {
            box-sizing: border-box;
        }
        .reset-pwd-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .reset-pwd-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 9999px;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .reset-pwd-input-group {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 6px;
        }
        .reset-pwd-input {
            flex: 1;
            width: 100%;
            padding: 10px 14px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            color: #0f172a;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.15s ease-in-out;
        }
        .reset-pwd-input:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.15);
            background-color: #ffffff;
        }
        .reset-pwd-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
            text-decoration: none;
        }
        .reset-pwd-btn-amber {
            background-color: #d97706;
            color: #ffffff;
        }
        .reset-pwd-btn-amber:hover {
            background-color: #b45309;
        }
        .reset-pwd-btn-navy {
            background-color: #1e3a8a;
            color: #ffffff;
        }
        .reset-pwd-btn-navy:hover {
            background-color: #1e40af;
        }
        .reset-pwd-btn-secondary {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .reset-pwd-btn-secondary:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .reset-pwd-notice {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-top: 16px;
            margin-bottom: 16px;
        }
        .reset-pwd-icon-small {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            max-width: 16px !important;
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }
        .reset-pwd-icon-notice {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            max-width: 18px !important;
            margin-top: 2px;
            color: #d97706;
            flex-shrink: 0;
        }
        .reset-pwd-spinner {
            animation: reset-pwd-spin 1s linear infinite;
        }
        @keyframes reset-pwd-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <!-- STEP 1: CONFIRMATION MODAL -->
    <template x-if="!generated">
        <div>
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">
                    Generate Reset Password Link?
                </h3>
                <p style="font-size: 14px; color: #475569; margin: 0; line-height: 1.6;">
                    Anda akan membuat link reset password untuk <strong>{{ $record->name }}</strong> (<span style="color: #64748b;">{{ $record->email }}</span>).
                </p>
            </div>

            <div x-show="errorMsg" x-cloak style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #991b1b; margin-bottom: 16px;">
                <span x-text="errorMsg"></span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <button
                    type="button"
                    x-on:click="closeModal($el)"
                    class="reset-pwd-btn reset-pwd-btn-secondary"
                >
                    Batal
                </button>
                <button
                    type="button"
                    x-on:click="generateLink()"
                    :disabled="loading"
                    class="reset-pwd-btn reset-pwd-btn-amber"
                    :style="loading ? 'opacity: 0.6; cursor: not-allowed;' : ''"
                >
                    <svg x-show="loading" class="reset-pwd-icon-small reset-pwd-spinner" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Membuat Link...' : 'Generate Link'"></span>
                </button>
            </div>
        </div>
    </template>

    <!-- STEP 2: DISPLAY GENERATED LINK (Result) -->
    <template x-if="generated">
        <div>
            <!-- Target User Information Card -->
            <div class="reset-pwd-card">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px;">
                    Pengguna Sasaran
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <span style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $record->name }}</span>
                        <span style="font-size: 13px; color: #64748b; margin-left: 4px;">({{ $record->email }})</span>
                    </div>
                    <span class="reset-pwd-badge">
                        Link Siap Digunakan
                    </span>
                </div>
            </div>

            <!-- Read-Only Reset URL with Copy Button -->
            <div style="margin-bottom: 16px;">
                <label for="generated-reset-password-url-field-{{ $record->id }}" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                    Link Reset Password
                </label>
                <div class="reset-pwd-input-group">
                    <input
                        id="generated-reset-password-url-field-{{ $record->id }}"
                        type="text"
                        readonly
                        :value="resetUrl"
                        class="reset-pwd-input"
                        onclick="this.select()"
                    />
                    <button
                        type="button"
                        x-on:click="copyToClipboard()"
                        class="reset-pwd-btn reset-pwd-btn-navy"
                    >
                        <svg class="reset-pwd-icon-small" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>Salin Link</span>
                    </button>
                </div>

                <!-- Copy Feedback Alerts -->
                <div x-show="copied" x-cloak style="margin-top: 8px; display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #059669;">
                    <svg class="reset-pwd-icon-small" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Link berhasil disalin.</span>
                </div>
                <div x-show="copyError" x-cloak style="margin-top: 8px; font-size: 12px; color: #b45309;">
                    Tidak dapat menyalin secara otomatis. Silakan blok dan salin link secara manual dari kolom teks di atas.
                </div>
            </div>

            <!-- Expiration & Security Info -->
            <div class="reset-pwd-notice">
                <svg class="reset-pwd-icon-notice" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div style="font-size: 13px; line-height: 1.5;">
                    <p style="font-weight: 700; color: #78350f; margin: 0 0 3px 0;">
                        Masa Berlaku Link: <span x-text="expireMinutes"></span> Menit
                    </p>
                    <p style="color: #92400e; margin: 0; font-size: 12px;">
                        Link ini hanya berlaku satu kali penggunaan. Segera kirimkan tautan ini kepada pengguna melalui WhatsApp resmi gereja. Jangan simpan atau bagikan tautan ini kepada pihak lain demi keamanan akun.
                    </p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <button
                    type="button"
                    x-on:click="closeModal($el)"
                    class="reset-pwd-btn reset-pwd-btn-secondary"
                >
                    Selesai / Tutup
                </button>
            </div>
        </div>
    </template>
</div>
