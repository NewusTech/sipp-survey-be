<table border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="9" style="text-align: center;">FORM SURVEY DATABASE DRAINASE</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>LOKASI</td>
            <td>: {{ $survey ? $survey[0]->nama_desa : '' }}</td>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>KECAMATAN</td>
            <td>: {{ $survey ? $survey[0]->nama_kecamatan : '' }}</td>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
            <td>NO</td>
            <td>NAMA RUAS</td>
            <td>PANJANG RUAS (M)</td>
            <td>PANJANG DRAINASE (M)</td>
            <td>LETAK DRAINASE (STA)</td>
            <td colspan="3" style="text-align: center;">DIMENSI</td>
            <td>KONDISI</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>Lebar Atas (CM)</td>
            <td>Lebar Bawah (CM)</td>
            <td>Tinggi (CM)</td>
            <td>&nbsp;</td>
        </tr>
        @foreach ($survey as $item)
        <tr>
            <td></td>
            <td>{{ $item->nama_ruas }}</td>
            <td>{{ $item->panjang_ruas }}</td>
            <td>{{ $item->panjang_drainase }}</td>
            <td>{{ $item->letak_drainase }}</td>
            <td>{{ $item->lebar_atas }}</td>
            <td>{{ $item->lebar_bawah }}</td>
            <td>{{ $item->tinggi }}</td>
            <td>{{ $item->kondisi }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td>TOTAL PANJANG RUAS (M)</td>
            <td>{{ $data_total_panjang_ruas }}</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td>TOTAL PANJANG DRAINASE (M)</td>
            <td>{{ $data_total_panjang_drainase }}</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td>TOTAL PANJANG DRAINASE KONDISI TANAH (M)</td>
            <td colspan="6">{{ $data_total_panjang_drainase_kondisi_tanah }}</td>
        </tr>
    </tbody>
</table>
