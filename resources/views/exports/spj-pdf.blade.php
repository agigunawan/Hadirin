<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Daftar Hadir - {{ $event->title }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #047857;
            padding-bottom: 8px;
        }

        .header .agency-name {
            font-size: 12pt;
            font-weight: bold;
            color: #064e3b;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .header .doc-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e293b;
            margin: 2px 0 6px 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .header .event-meta {
            font-size: 8.5pt;
            color: #334155;
            margin: 2px 0;
        }

        .event-meta strong {
            color: #0f172a;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #047857;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
            letter-spacing: 0.2px;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .sig-container {
            text-align: center;
            width: 100%;
            height: 32px;
            line-height: 32px;
        }

        .sig-image {
            max-height: 30px;
            max-width: 90px;
            display: inline-block;
            vertical-align: middle;
        }

        .pengesahan-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .pengesahan-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .pengesahan-box {
            text-align: center;
            width: 320px;
            font-size: 8.5pt;
            float: right;
        }

        .pengesahan-date {
            margin-bottom: 3px;
        }

        .pengesahan-role {
            font-weight: bold;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .pengesahan-sig-wrapper {
            height: 55px;
            line-height: 55px;
            margin: 4px 0;
            text-align: center;
        }

        .pengesahan-sig-image {
            max-height: 50px;
            max-width: 150px;
            vertical-align: middle;
            display: inline-block;
        }

        .pengesahan-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 4px;
            color: #0f172a;
        }

        .pengesahan-nip {
            font-size: 8pt;
            color: #334155;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="agency-name">BALAI BESAR KEKARANTINAAN KESEHATAN TANJUNG PRIOK</div>
        <div class="doc-title">REKAPITULASI DAFTAR HADIR PERTEMUAN</div>
        <div class="event-meta">
            <strong>Kegiatan:</strong> {{ $event->title }}
        </div>
        <div class="event-meta">
            <strong>Tanggal:</strong> {{ $event->event_date->translatedFormat('d F Y') }}
            &nbsp;|&nbsp;
            <strong>Waktu:</strong> {{ $event->formatted_time }}
            &nbsp;|&nbsp;
            <strong>Ruang:</strong> {{ $event->room->name ?? '-' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $col['heading'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($columns as $col)
                        @php
                            $colId = $col['id'];
                            $val = $row[$colId] ?? '-';
                        @endphp

                        @if($colId === 'no')
                            <td class="text-center" style="width: 25px;">{{ $val }}</td>
                        @elseif($colId === 'signature')
                            <td class="text-center" style="width: 95px; padding: 2px;">
                                @if(!empty($val) && str_starts_with($val, 'data:image'))
                                    <div class="sig-container">
                                        <img src="{{ $val }}" alt="TTD" class="sig-image">
                                    </div>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                        @elseif($colId === 'waktu')
                            <td class="text-center nowrap" style="width: 100px;">{{ $val }}</td>
                        @elseif(in_array($colId, ['nip_or_id', 'phone', 'bank_account_no', 'coordinates', 'gender']))
                            <td class="text-center">{{ $val }}</td>
                        @else
                            <td class="text-left">{{ $val }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center" style="padding: 18px; color: #64748b; font-style: italic;">
                        Belum ada data peserta yang terdaftar pada kegiatan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pengesahan Pimpinan Kedinasan -->
    <table class="pengesahan-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                <div class="pengesahan-box">
                    <div class="pengesahan-date">{{ $pimpinan['date_text'] ?? ('Jakarta, ' . $event->event_date->translatedFormat('d F Y')) }}</div>
                    <div class="pengesahan-role">{{ $pimpinan['position'] }}</div>

                    <div class="pengesahan-sig-wrapper">
                        @if(!empty($pimpinan['signature']) && str_starts_with($pimpinan['signature'], 'data:image'))
                            <img src="{{ $pimpinan['signature'] }}" alt="TTD Pengesahan" class="pengesahan-sig-image">
                        @elseif(!empty($pimpinan['signature']))
                            <img src="{{ $pimpinan['signature'] }}" alt="TTD Pengesahan" class="pengesahan-sig-image">
                        @else
                            <div style="height: 45px;"></div>
                        @endif
                    </div>

                    <div class="pengesahan-name">{{ $pimpinan['name'] ?: '....................................' }}</div>
                    <div class="pengesahan-nip">NIP. {{ $pimpinan['nip'] ?: '........................' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
