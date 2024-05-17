<center><h2>DATA DASAR PRASARANA JALAN KABUPATEN TULANG BAWANG BARAT</h2></center>
<table>
    <tbody>
        <tr>
            <td>PROVINSI : LAMPUNG</td>
        </tr>
        <tr>
            <td>KABUPATEN : TULANG BAWANG BARAT</td>
        </tr>
        <tr>
            <td>TAHUN : {{ $survey[0]->tahun }}</td>
        </tr>
    </tbody>
</table>
<table style="border: 1px solid black;">
    <thead>
    <tr>
        <th style="border: 1px solid black;"><strong>NO. RUAS</strong></th>
        <th style="border: 1px solid black;"><strong>NAMA RUAS JALAN</strong></th>
        <th style="border: 1px solid black;"><strong>KECAMATAN YANG DILALUI</strong></th>
        <th style="border: 1px solid black;"><strong>PANJANG RUAS (km)</strong></th>
        <th style="border: 1px solid black;"><strong>LEBAR RUAS (km)</strong></th>
        <th style="border: 1px solid black;"><strong>Aspal Hotmix</strong></th>
        <th style="border: 1px solid black;"><strong>Beton</strong></th>
        <th style="border: 1px solid black;"><strong>Lapen/Latasir</strong></th>
        <th style="border: 1px solid black;"><strong>Telford/kerikil</strong></th>
        <th style="border: 1px solid black;"><strong>Tanah</strong></th>
        <th style="border: 1px solid black;"><strong>BAIK</strong></th>
        <th style="border: 1px solid black;"><strong>SEDANG</strong></th>
        <th style="border: 1px solid black;"><strong>RUSAK RINGAN</strong></th>
        <th style="border: 1px solid black;"><strong>RUSAK BERAT</strong></th>
        <th style="border: 1px solid black;"><strong>LHR</strong></th>
        <th style="border: 1px solid black;"><strong>AKSES KE N/P/K</strong></th>
        <th style="border: 1px solid black;"><strong>KET</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($survey as $item)
        <tr>
            <td style="border: 1px solid black;">{{ $item->no_ruas }}</td>
            <td style="border: 1px solid black;">{{ $item->nama_ruas }}</td>
            <td style="border: 1px solid black;">{{ $item->name_kecamatan }}</td>
            <td style="border: 1px solid black;">{{ $item->panjang_ruas }}</td>
            <td style="border: 1px solid black;">{{ $item->lebar }}</td>
            <td style="border: 1px solid black;">{{ $item->hotmix }}</td>
            <td style="border: 1px solid black;">{{ $item->rigit }}</td>
            <td style="border: 1px solid black;">{{ $item->lapen }}</td>
            <td style="border: 1px solid black;">{{ $item->telford }}</td>
            <td style="border: 1px solid black;">{{ $item->tanah }}</td>
            <td style="border: 1px solid black;">{{ $item->baik }}</td>
            <td style="border: 1px solid black;">{{ $item->sedang }}</td>
            <td style="border: 1px solid black;">{{ $item->rusak_ringan }}</td>
            <td style="border: 1px solid black;">{{ $item->rusak_berat }}</td>
            <td style="border: 1px solid black;">{{ $item->lhr }}</td>
            <td style="border: 1px solid black;">{{ $item->akses }}</td>
            <td style="border: 1px solid black;">{{ $item->keterangan }}</td>
        </tr>
    @endforeach
    </tbody>
</table>