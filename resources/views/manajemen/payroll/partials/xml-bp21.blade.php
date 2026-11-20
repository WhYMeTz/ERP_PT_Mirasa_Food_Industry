{{-- Unduh XML BP21 (PPh 21) untuk bagian yang sedang dibuka. Dipanggil dari multi.blade.php:
     @include('manajemen.payroll.partials.xml-bp21', ['periode' => $periode, 'bagian' => $bagian, 'remarks1' => $isian['remarks1']]) --}}
@php
    $awalBulan = \Carbon\Carbon::createFromFormat('!Y-m', $periode);
    $nomorDok  = $awalBulan->month . $awalBulan->year . '-' . $remarks1;           // contoh: 82026-AFKIR
    $tglDok    = $awalBulan->copy()->endOfMonth()->format('Y-m-d');                 // akhir bulan
    $tglPotong = $awalBulan->copy()->addMonth()->startOfMonth()->format('Y-m-d');   // tanggal 1 bulan berikutnya
    $desember  = $awalBulan->month === 12;
@endphp

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.25rem;margin-top:1.25rem;">
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.35rem;">
        <span style="width:28px;height:28px;border-radius:8px;background:#6366f1;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;">XML</span>
        <strong style="color:#0f172a;">Bukti Potong PPh 21 (BP21)</strong>
    </div>
    <p style="margin:0 0 .85rem;color:#64748b;font-size:.82rem;">
        Berkas XML untuk unggah massal di Coretax, bagian <strong>{{ $bagian }}</strong> periode {{ $awalBulan->locale('id')->translatedFormat('F Y') }}.
        Penghasilan = gaji bersih yang ditransfer, tarif = TER menurut status PTKP.
        Hanya karyawan yang <strong>dicentang</strong> pada pratinjau di atas yang masuk XML.
        Setiap karyawan harus sudah punya NPWP/NIK 16 digit dan Status PTKP di Master Karyawan.
    </p>

    @if ($desember)
        <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;border-radius:8px;padding:.6rem .85rem;font-size:.82rem;">
            Masa pajak Desember memakai perhitungan tahunan (bukan TER), jadi XML belum bisa dibuat dari sini.
        </div>
    @else
        <form id="formXmlBp21" method="GET" action="{{ route('manajemen.payroll.multi.xml') }}" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="bagian" value="{{ $bagian }}">

            <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;color:#475569;">
                Nomor dokumen
                <input type="text" name="nomor_dokumen" value="{{ $nomorDok }}" maxlength="50" required class="form-control" style="padding:.45rem .6rem;min-width:170px;">
            </label>
            <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;color:#475569;">
                Tanggal dokumen
                <input type="date" name="tanggal_dokumen" value="{{ $tglDok }}" required class="form-control" style="padding:.45rem .6rem;">
            </label>
            <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;color:#475569;">
                Tanggal pemotongan
                <input type="date" name="tanggal_pemotongan" value="{{ $tglPotong }}" required class="form-control" style="padding:.45rem .6rem;">
            </label>

            <button type="submit" class="btn btn-primary" style="height:38px;">Unduh XML BP21</button>
            <span id="xmlJumlah" style="font-size:.82rem;color:#475569;align-self:center;"></span>
        </form>

        <div id="xmlDaftar" style="margin-top:.9rem;">
            <div style="font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:.4rem;">
                Karyawan yang masuk file XML (<span id="xmlDaftarJumlah">0</span>)
            </div>
            <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                    <thead>
                        <tr style="background:#f8fafc;color:#475569;text-align:left;">
                            <th style="padding:.45rem .7rem;width:50px;">No</th>
                            <th style="padding:.45rem .7rem;">Nama</th>
                            <th style="padding:.45rem .7rem;">Rekening</th>
                            <th style="padding:.45rem .7rem;text-align:right;">Gaji Bersih (Penghasilan BP21)</th>
                        </tr>
                    </thead>
                    <tbody id="xmlDaftarBody"></tbody>
                </table>
            </div>
        </div>

        <script>
            (function () {
                const form  = document.getElementById('formXmlBp21');
                const label = document.getElementById('xmlJumlah');
                const semua = () => Array.from(document.querySelectorAll('input[name="ikut[]"]'));
                const esc = s => String(s).replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));

                function isiDaftar() {
                    const body = document.getElementById('xmlDaftarBody');
                    const rows = semua().filter(c => c.checked).map(c => c.closest('tr')).filter(Boolean);
                    const kolom = {};
                    if (rows.length) {
                        const tabel = rows[0].closest('table');
                        (tabel ? Array.from(tabel.querySelectorAll('thead th')) : []).forEach((th, i) => { kolom[th.textContent.trim().toLowerCase()] = i; });
                    }
                    const ambil = (tr, nama, cadangan) => {
                        const td = tr.children[kolom[nama] !== undefined ? kolom[nama] : cadangan];
                        return td ? td.textContent.trim() : '';
                    };
                    document.getElementById('xmlDaftarJumlah').textContent = rows.length;
                    body.innerHTML = rows.length ? rows.map((tr, n) =>
                        '<tr style="border-top:1px solid #e2e8f0;">' +
                        '<td style="padding:.45rem .7rem;">' + (n + 1) + '</td>' +
                        '<td style="padding:.45rem .7rem;font-weight:600;">' + esc(ambil(tr, 'beneficiary', 5)) + '</td>' +
                        '<td style="padding:.45rem .7rem;font-family:monospace;">' + esc(ambil(tr, 'credited account', 4)) + '</td>' +
                        '<td style="padding:.45rem .7rem;text-align:right;">' + esc(ambil(tr, 'amount', 6)) + '</td>' +
                        '</tr>').join('')
                        : '<tr><td colspan="4" style="padding:.7rem;color:#b91c1c;text-align:center;">Belum ada karyawan yang dicentang.</td></tr>';
                }

                function hitung() {
                    isiDaftar();
                    const dicentang = semua().filter(c => c.checked).length;
                    label.textContent = semua().length ? dicentang + ' dari ' + semua().length + ' karyawan dicentang' : '';
                    label.style.color = dicentang === 0 ? '#b91c1c' : '#475569';
                }

                form.addEventListener('submit', function (e) {
                    const dicentang = semua().filter(c => c.checked);
                    if (dicentang.length === 0) {
                        e.preventDefault();
                        alert('Centang minimal satu karyawan pada pratinjau di atas.');
                        return;
                    }
                    form.querySelectorAll('input[data-xml-ikut]').forEach(i => i.remove());
                    const tambah = (nama, nilai) => {
                        const i = document.createElement('input');
                        i.type = 'hidden'; i.name = nama; i.value = nilai; i.dataset.xmlIkut = '1';
                        form.appendChild(i);
                    };
                    tambah('pilih', '1');
                    dicentang.forEach(c => tambah('ikut[]', c.value));
                });

                document.addEventListener('change', e => { if (e.target && e.target.type === 'checkbox') hitung(); });
                hitung();
            })();
        </script>
    @endif
</div>
