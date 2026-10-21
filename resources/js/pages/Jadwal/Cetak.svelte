<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Link } from "@inertiajs/svelte";
    import { ArrowLeft, Printer } from "@lucide/svelte";

    let { jadwalList = [], tahun } = $props();

    const hasSabtu = $derived(jadwalList.some((j: any) => j.hari === "Sabtu"));

    const days = $derived(
        hasSabtu
            ? [
                  { key: "Senin", label: "SENIN" },
                  { key: "Selasa", label: "SELASA" },
                  { key: "Rabu", label: "RABU" },
                  { key: "Kamis", label: "KAMIS" },
                  { key: "Jumat", label: "JUM'AT" },
                  { key: "Sabtu", label: "SABTU" },
              ]
            : [
                  { key: "Senin", label: "SENIN" },
                  { key: "Selasa", label: "SELASA" },
                  { key: "Rabu", label: "RABU" },
                  { key: "Kamis", label: "KAMIS" },
                  { key: "Jumat", label: "JUM'AT" },
              ],
    );

    function formatDotTime(t: string): string {
        if (!t) return "";
        return t.slice(0, 5).replace(":", ".");
    }

    function getWaktuLabel(jamMulai: string): string {
        const hhmm = jamMulai ? jamMulai.slice(0, 5) : "08:00";
        if (hhmm < "12:00") return "PAGI";
        if (hhmm < "15:30") return "SIANG";
        return "SORE";
    }

    const timeSlots = $derived.by(() => {
        const map = new Map<
            string,
            {
                key: string;
                jam_mulai: string;
                jam_selesai: string;
                waktu: string;
                byDay: Record<string, any[]>;
            }
        >();

        for (const item of jadwalList) {
            const jm = item.jam_mulai?.slice(0, 5) || "";
            const js = item.jam_selesai?.slice(0, 5) || "";
            const key = `${jm}-${js}`;

            if (!map.has(key)) {
                map.set(key, {
                    key,
                    jam_mulai: jm,
                    jam_selesai: js,
                    waktu: getWaktuLabel(jm),
                    byDay: {
                        Senin: [],
                        Selasa: [],
                        Rabu: [],
                        Kamis: [],
                        Jumat: [],
                        Sabtu: [],
                    },
                });
            }

            const slot = map.get(key)!;
            if (slot.byDay[item.hari]) {
                slot.byDay[item.hari].push(item);
            }
        }

        return Array.from(map.values());
    });

    function formatItemLabel(item: any): string {
        const nama = item.mata_pelajaran?.nama || "-";
        if (item.keterangan) {
            return `${nama} (${item.keterangan})`;
        }
        return nama;
    }
</script>

<AppHead title={`Cetak Jadwal Bimbingan dan Keterampilan Klien ${tahun}`} />

<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .no-print {
            display: none !important;
        }
        .print-sheet {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid #000;
        font-family: "Times New Roman", Times, serif;
        color: #000;
    }

    .print-table th,
    .print-table td {
        border: 1px solid #000;
        padding: 6px 8px;
        vertical-align: top;
        font-size: 13px;
        line-height: 1.35;
    }

    .print-table th {
        font-weight: bold;
        text-transform: uppercase;
        text-align: left;
    }
</style>

<div class="min-h-screen bg-muted/30 py-6 px-4 print:bg-white print:p-0">
    <!-- Action Toolbar (Hidden when printing) -->
    <div class="no-print mx-auto mb-6 flex max-w-6xl items-center justify-between rounded-xl border bg-card p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <Button variant="outline" size="sm" asChild>
                <Link href={`/jadwal?tahun=${tahun}`}>
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Jadwal
                </Link>
            </Button>
            <div>
                <h1 class="text-sm font-semibold">
                    Pratinjau Cetak Jadwal Tahun {tahun}
                </h1>
                <p class="text-xs text-muted-foreground">
                    Gunakan ukuran kertas A4 Landscape saat mencetak.
                </p>
            </div>
        </div>

        <Button onclick={() => window.print()}>
            <Printer class="mr-2 h-4 w-4" />
            Cetak / Simpan PDF
        </Button>
    </div>

    <!-- Printable Document Sheet -->
    <div
        class="print-sheet mx-auto max-w-6xl rounded-lg border bg-white p-10 text-black shadow-sm"
        style="font-family: 'Times New Roman', Times, serif;"
    >
        <!-- Kop / Header -->
        <div class="mb-6 text-center">
            <div class="border-b-[3px] border-double border-black pb-2 mb-5">
                <p class="text-sm font-bold uppercase tracking-wide">
                    PEMERINTAH PROVINSI SUMATERA SELATAN
                </p>
                <p class="text-sm font-bold uppercase tracking-wide">
                    DINAS SOSIAL
                </p>
                <h2 class="text-base font-bold uppercase tracking-wide">
                    UPTD PANTI SOSIAL REHABILITASI PENYANDANG DISABILITAS SENSORIK
                </h2>
            </div>

            <h1 class="text-base font-bold uppercase tracking-wide">
                JADWAL BIMBINGAN DAN KETERAMPILAN KLIEN
            </h1>
            <p class="mt-1 text-sm font-bold uppercase">
                UPTD PANTI SOSIAL REHABILITASI PENYANDANG DISABILITAS SENSORIK
            </p>
            <p class="text-sm font-bold uppercase">
                TAHUN ANGGARAN {tahun}
            </p>
        </div>

        <!-- Schedule Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 36px; text-align: center;">NO</th>
                    <th style="width: 75px;">WAKTU</th>
                    <th style="width: 75px; text-align: center;">JAM</th>
                    {#each days as d (d.key)}
                        <th>{d.label}</th>
                    {/each}
                </tr>
            </thead>
            <tbody>
                {#if timeSlots.length === 0}
                    <tr>
                        <td colspan={3 + days.length} style="text-align: center; padding: 24px;">
                            Belum ada data jadwal pada tahun anggaran {tahun}.
                        </td>
                    </tr>
                {:else}
                    {#each timeSlots as slot, index (slot.key)}
                        <tr>
                            <td style="text-align: center;">{index + 1}.</td>
                            <td>{slot.waktu}</td>
                            <td style="text-align: center;">
                                <div>{formatDotTime(slot.jam_mulai)}</div>
                                <div>s/d</div>
                                <div>{formatDotTime(slot.jam_selesai)}</div>
                            </td>
                            {#each days as d (d.key)}
                                {@const cellItems = slot.byDay[d.key] || []}
                                <td>
                                    {#if cellItems.length === 1}
                                        <div>{formatItemLabel(cellItems[0])}</div>
                                    {:else if cellItems.length > 1}
                                        <div class="space-y-1">
                                            {#each cellItems as item, idx (item.id)}
                                                <div>
                                                    {idx + 1}.{formatItemLabel(item)}
                                                </div>
                                            {/each}
                                        </div>
                                    {/if}
                                </td>
                            {/each}
                        </tr>
                    {/each}
                {/if}
            </tbody>
        </table>

        <!-- Signature Footer -->
        <div class="mt-10 grid grid-cols-2 gap-8 text-center text-[13px] leading-snug">
            <div>
                <p>Kepala UPTD Panti Sosial Rehabilitasi</p>
                <p>Penyandang Disabilitas Sensorik</p>
                <div class="h-20"></div>
                <p class="font-bold underline">
                    H. Deddy Ismail, A.Md.Kep, SE, MM
                </p>
                <p>Pembina (IV/A)</p>
                <p>NIP. 19680411 199103 1 004</p>
            </div>

            <div>
                <p>Kasie Rehabilitasi Sosial</p>
                <p>UPTD Panti Sosial Rehabilitasi</p>
                <p>Penyandang Disabilitas Sensorik</p>
                <div class="h-16"></div>
                <p class="font-bold underline">
                    Mustaq Lutfi, S.Sos, M.Si
                </p>
                <p>Pembina (IV/A)</p>
                <p>NIP. 19701014 199003 1 001</p>
            </div>
        </div>
    </div>
</div>
