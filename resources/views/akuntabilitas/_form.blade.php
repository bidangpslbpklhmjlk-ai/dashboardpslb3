<div class="row">

    <div class="col-md-8">

        <div class="mb-3">
            <label>Judul Dokumen</label>

            <input
                type="text"
                name="judul"
                class="form-control"
                value="{{ old('judul', $item->judul ?? '') }}"
                required>
        </div>

        <div class="mb-3">

            <label>Jenis Dokumen</label>

            <select
                name="jenis"
                class="form-control"
                required>

                <option value="">Pilih Jenis</option>

                @foreach([
                    'SAKIP',
                    'LKjIP',
                    'RENSTRA',
                    'RENJA',
                    'PERJANJIAN_KINERJA',
                    'SOP',
                    'STANDAR_PELAYANAN',
                    'MAKLUMAT_PELAYANAN',
                    'PUBLIKASI',
                    'REGULASI',
                    'LAINNYA'
                ] as $jenis)

                    <option
                        value="{{ $jenis }}"
                        @selected(old('jenis', $item->jenis ?? '') == $jenis)>

                        {{ $jenis }}

                    </option>

                @endforeach

            </select>

        </div>

        <div class="mb-3">

            <label>Tahun</label>

            <input
                type="number"
                name="tahun"
                class="form-control"
                value="{{ old('tahun', $item->tahun ?? date('Y')) }}"
                required>

        </div>

        <div class="mb-3">

            <label>File PDF</label>

            <input
                type="file"
                name="file"
                class="form-control">

            @if(isset($item))

                <small class="text-success">

                    File saat ini:
                    {{ $item->nama_file }}

                </small>

            @endif

        </div>

        <div class="form-check">

            <input
                type="checkbox"
                name="is_publish"
                value="1"
                class="form-check-input"
                {{ old('is_publish', $item->is_publish ?? true) ? 'checked' : '' }}>

            <label class="form-check-label">

                Publish ke Website

            </label>

        </div>

    </div>

</div>