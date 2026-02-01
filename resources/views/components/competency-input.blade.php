<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Persyaratan Kompetensi</h5>
    </div>
    <div class="card-body">
        
        <div class="table-responsive">
            <table class="table table-bordered" id="competency-table">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 35%">Nama Kompetensi</th>
                        <th style="width: 15%">Level Ideal (1-5)</th>
                        <th style="width: 45%">Perilaku Kunci (Key Behaviors)</th>
                        <th style="width: 5%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="competency-container">
                    {{-- JIKA EDIT: Loop data yang sudah ada --}}
                    @if(isset($existingCompetencies) && count($existingCompetencies) > 0)
                        @foreach($existingCompetencies as $index => $comp)
                            <tr class="competency-row" data-index="{{ $index }}">
                                <td>
                                    <input type="hidden" name="competencies[{{ $index }}][competency_master_id]" value="{{ $comp->competency_master_id }}">
                                    <input type="hidden" name="competencies[{{ $index }}][competency_name]" value="{{ $comp->competency_name }}">
                                    
                                    <input type="text" class="form-control bg-light" value="{{ $comp->competency_name }}" readonly>
                                    
                                    <textarea style="display:none" class="behaviors-data">
                                        {{ json_encode($comp->competencyMaster->keyBehaviors ?? []) }}
                                    </textarea>
                                </td>
                                <td>
                                    <select name="competencies[{{ $index }}][ideal_level]" class="form-control level-select">
                                        @for($i=1; $i<=5; $i++)
                                            <option value="{{ $i }}" {{ $comp->ideal_level == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td class="behavior-display bg-light text-small">
                                    <ul class="pl-3 mb-0">Loading...</ul>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row mt-3 p-3 bg-light border rounded">
            <div class="col-md-10">
                <label>Tambah Kompetensi Baru:</label>
                <input type="text" id="search-competency" class="form-control" placeholder="Ketik nama kompetensi (misal: Strategic Management)...">
                <div id="search-results" class="list-group position-absolute w-100" style="z-index: 1000; display:none;"></div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = {{ isset($existingCompetencies) ? count($existingCompetencies) : 0 }};
    const container = document.getElementById('competency-container');
    const searchInput = document.getElementById('search-competency');
    const searchResults = document.getElementById('search-results');

    // 1. FUNGSI PENCARIAN (Autocomplete)
    searchInput.addEventListener('input', function() {
        let query = this.value;
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Panggil API searchCompetencies di Controller
        fetch(`/admin/job-profile/search-competencies?q=${query}`)
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.length > 0) {
                    searchResults.style.display = 'block';
                    data.forEach(item => {
                        let a = document.createElement('a');
                        a.classList.add('list-group-item', 'list-group-item-action');
                        a.innerText = item.competency_name + ' (' + (item.type ?? 'General') + ')';
                        a.href = "#";
                        
                        // Saat diklik, tambahkan ke tabel
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            addCompetencyRow(item);
                            searchInput.value = '';
                            searchResults.style.display = 'none';
                        });
                        searchResults.appendChild(a);
                    });
                } else {
                    searchResults.style.display = 'none';
                }
            });
    });

    // 2. FUNGSI MENAMBAH BARIS KE TABEL
    function addCompetencyRow(data) {
        // Simpan JSON behaviors ke dalam atribut data atau textarea hidden
        // Agar nanti bisa dibaca saat ganti level
        let behaviorsJson = JSON.stringify(data.key_behaviors);

        let row = `
            <tr class="competency-row" data-index="${rowIndex}">
                <td>
                    <input type="hidden" name="competencies[${rowIndex}][competency_master_id]" value="${data.id}">
                    <input type="hidden" name="competencies[${rowIndex}][competency_name]" value="${data.competency_name}">
                    <input type="text" class="form-control bg-light" value="${data.competency_name}" readonly>
                    <textarea style="display:none" class="behaviors-data">${behaviorsJson}</textarea>
                </td>
                <td>
                    <select name="competencies[${rowIndex}][ideal_level]" class="form-control level-select">
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3</option>
                        <option value="4">Level 4</option>
                        <option value="5">Level 5</option>
                    </select>
                </td>
                <td class="behavior-display bg-light text-small">
                    <ul class="pl-3 mb-0 text-muted">Pilih level...</ul>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        container.insertAdjacentHTML('beforeend', row);
        
        // Trigger update teks perilaku untuk level default (1)
        updateBehaviorText(container.lastElementChild);
        
        rowIndex++;
    }

    // 3. EVENT LISTENER: HAPUS BARIS
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    // 4. EVENT LISTENER: GANTI LEVEL -> GANTI TEKS PERILAKU
    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('level-select')) {
            let row = e.target.closest('tr');
            updateBehaviorText(row);
        }
    });

    // 5. FUNGSI UPDATE TEKS BERDASARKAN LEVEL
    function updateBehaviorText(row) {
        let level = row.querySelector('.level-select').value;
        let displayBox = row.querySelector('.behavior-display ul');
        let behaviorsData = row.querySelector('.behaviors-data').value;
        
        try {
            let allBehaviors = JSON.parse(behaviorsData);
            
            // Filter perilaku sesuai level yang dipilih
            // Pastikan tipe data level sama (integer)
            let filtered = allBehaviors.filter(b => b.level == level);

            displayBox.innerHTML = ''; // Kosongkan dulu

            if (filtered.length > 0) {
                filtered.forEach(b => {
                    let li = document.createElement('li');
                    li.innerText = b.behavior; // Kolom 'behavior' dari DB
                    displayBox.appendChild(li);
                });
            } else {
                displayBox.innerHTML = '<li class="text-danger">Belum ada data perilaku kunci untuk level ini.</li>';
            }
        } catch (error) {
            console.error("Gagal parsing JSON behaviors", error);
            displayBox.innerHTML = '<li>Error memuat detail perilaku.</li>';
        }
    }

    // 6. INISIALISASI SAAT EDIT (Load teks untuk data yang sudah ada)
    document.querySelectorAll('.competency-row').forEach(row => {
        updateBehaviorText(row);
    });
});
</script>

<style>
    /* Sedikit styling agar list perilaku tidak terlalu memakan tempat */
    .behavior-display ul {
        font-size: 0.85rem;
        line-height: 1.4;
        margin-left: 15px;
    }
    #search-results {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
    }
</style>