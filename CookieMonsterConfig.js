$(document).ready(function() {
    // instantiate WireTabs if defined
    $('#ModuleEditForm').WireTabs({
        items: $("#ModuleEditForm > .Inputfields > .InputfieldWrapper"),
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('google-ads-conversion-wrapper');
    const hiddenField = document.getElementById('google_ads_conversions');
    const addButton = document.querySelector('.add-conversion');
    let conversions = [];
    try {
        conversions = JSON.parse(hiddenField.value || '[]');
    } catch (e) {
        conversions = [];
    }
    const render = () => {
        wrapper.innerHTML = '';
        conversions.forEach((conv, index) => {
            const div = document.createElement('div');
            div.className = 'conversion-entry';
            div.innerHTML = `
                <label>Label: <input type="text" class="label" value="${conv.label || ''}"></label>
                <label>ID: <input type="text" class="id" value="${conv.id || ''}"></label>
                <label>Description: <input type="text" class="desc" value="${conv.description || ''}"></label>
                <label>Active: <input type="checkbox" class="active" ${conv.active ? 'checked' : ''}></label>
                <div class="pages-wrapper">
                    <label>Pages:
                        <select class="pages-select" multiple></select>
                    </label>
                </div>
                <button type="button" class="remove-conversion">Entfernen</button>
                <hr>
            `;
            wrapper.appendChild(div);
            div.querySelector('.remove-conversion').addEventListener('click', () => {
                conversions.splice(index, 1);
                render();
                sync();
            });
            ['input', 'change'].forEach(eventType => {
                div.querySelectorAll('input').forEach(input => {
                    input.addEventListener(eventType, () => {
                        conversions[index] = {
                            label: div.querySelector('.label').value,
                            id: div.querySelector('.id').value,
                            description: div.querySelector('.desc').value,
                            active: div.querySelector('.active').checked,
                            pages: conversions[index].pages || []
                        };
                        sync();
                    });
                });
            });
            const select = div.querySelector('.pages-select');
            fetch('/cookie-monster/pages-json')
                .then(response => response.json())
                .then(data => {
                    $(select).select2({
                        placeholder: 'Wähle Seiten aus',
                        data: data.map(p => ({
                            id: p.id.toString(),
                            text: '\u00a0'.repeat((p.level - 1) * 2) + p.title + ' (/' + p.path + ')'
                        })),
                        width: 'resolve',
                        dropdownAutoWidth: true,
                        templateResult: page => page.id ? page.text : page.text,
                        templateSelection: page => page.title || page.text,
                        escapeMarkup: m => m
                    });
                    if (conv.pages && Array.isArray(conv.pages)) {
                        const pageIds = conv.pages.map(id => id.toString());
                        $(select).val(pageIds).trigger('change');
                    }
                    $(select).on('change', () => {
                        conversions[index].pages = $(select).val().map(id => parseInt(id));
                        sync();
                    });
                })
                .catch(err => {
                    console.error(err);
                });
        });
    };
    const sync = () => {
        hiddenField.value = JSON.stringify(conversions);
    };
    addButton.addEventListener('click', () => {
        conversions.push({ label: '', id: '', description: '', active: false, pages: [] });
        render();
        sync();
    });
    render();
    sync();
});