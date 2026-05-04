document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('question-type');
    const optionsSection = document.getElementById('options-section');
    const optionsList = document.getElementById('options-list');
    const addOptionBtn = document.getElementById('add-option');
    const shortSection = document.getElementById('short-answer-section');
    const longSection = document.getElementById('long-answer-section');
    const optionsPayload = document.getElementById('options_payload');
    const typeHint = document.getElementById('question-type-hint');

    function ensureTrueFalseOptions() {
        if (optionsList.querySelectorAll('.option-row').length === 0) {
            renderOptionRow(Date.now(), 'True', true);
            renderOptionRow(Date.now() + 1, 'False', false);
        }
    }

    function renderOptionRow(idx, label = '', isCorrect = false) {
        const row = document.createElement('div');
        row.className = 'option-row';
        row.dataset.idx = idx;
        row.innerHTML = `
            <input type="text" class="option-label" placeholder="Option text" value="${label}" />
            <label style="margin-left:8px"><input type="checkbox" class="option-correct" ${isCorrect ? 'checked' : ''} /> Correct</label>
            <button type="button" class="remove-option btn btn-sm btn-outline-danger" style="margin-left:8px">Remove</button>
        `;

        optionsList.appendChild(row);
    }

    function refreshPayload() {
        const rows = Array.from(optionsList.querySelectorAll('.option-row'));
        const payload = rows.map(r => ({
            label: r.querySelector('.option-label').value,
            is_correct: !!r.querySelector('.option-correct').checked,
        }));
        optionsPayload.value = JSON.stringify(payload);
    }

    function showForType(type) {
        const showOptions = ['single_choice', 'multiple_choice', 'boolean'].includes(type);
        optionsSection.style.display = showOptions ? 'block' : 'none';
        shortSection.style.display = (type === 'short_answer') ? 'block' : 'none';
        longSection.style.display = (type === 'long_answer') ? 'block' : 'none';

        if (type === 'boolean') {
            ensureTrueFalseOptions();
        }

        if (typeHint) {
            const labels = {
                boolean: 'True / False',
                single_choice: 'MCQ (Single)',
                multiple_choice: 'MCQ (Multiple)',
                short_answer: 'Single Line Answer',
                long_answer: 'Long Answer',
            };
            typeHint.textContent = labels[type] ? `Selected: ${labels[type]}` : '';
        }
    }

    typeSelect.addEventListener('change', function () {
        showForType(this.value);
    });

    addOptionBtn.addEventListener('click', function () {
        renderOptionRow(Date.now());
    });

    optionsList.addEventListener('click', function (e) {
        if (e.target.matches('.remove-option')) {
            const row = e.target.closest('.option-row');
            row.remove();
            refreshPayload();
        }
    });

    optionsList.addEventListener('input', function () {
        refreshPayload();
    });

    optionsList.addEventListener('change', function (e) {
        if (e.target.matches('.option-correct') && typeSelect.value === 'single_choice') {
            optionsList.querySelectorAll('.option-correct').forEach(cb => {
                if (cb !== e.target) {
                    cb.checked = false;
                }
            });
        }
        refreshPayload();
    });

    // initialize
    showForType(typeSelect.value);
});
