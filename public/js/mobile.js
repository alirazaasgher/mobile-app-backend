let colorCounter = 1;
$(document).ready(function () {

    $('.tab-btn').on('click', function (e) {
        e.preventDefault();
        const targetTab = $(this).data('tab');

        // Remove active state from all tabs
        $('.tab-btn')
            .removeClass('border-blue-600 text-blue-600 bg-blue-50')
            .addClass('border-transparent text-gray-600')
            .attr('aria-selected', 'false');

        // Add active state to clicked tab
        $(this)
            .addClass('border-blue-600 text-blue-600 bg-blue-50')
            .removeClass('border-transparent text-gray-600')
            .attr('aria-selected', 'true');

        // Hide all tab contents
        $('.tab-content').addClass('hidden');

        // Show target tab content
        $('#tab-' + targetTab).removeClass('hidden');
    });
    document.getElementById('variants-wrapper').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-row').remove();
        }
    });

    $(document).on('click', '#addColorBtn', function () {
        colorCounter++;
        const colorId = `color_${colorCounter}`;
        const colorSlug = colorId.toLowerCase();

        const html = `
        <div class="color-option-row flex items-center space-x-2 w-full border p-2 rounded" id="${colorId}">
            <input type="checkbox" name="variants[colors][]" value="${colorSlug}" class="rounded flex-shrink-0" checked>
            
            <div class="color-preview w-4 h-4 rounded-full border flex-shrink-0" style="background-color: #000000"></div>

            <input type="text" name="variants[color_names][${colorSlug}]" placeholder="Color Name"
                class="color-name-input text-sm border rounded px-2 py-1 flex-1">

            <input type="text" name="variants[color_hex][${colorSlug}]" value="#000000"
                class="color-hex-input text-xs border rounded px-2 py-1 flex-1"
                oninput="updateColorCircle('${colorId}', this.value)">

            <input type="file" name="variants[color_image][${colorSlug}][]"
                class="text-xs border rounded px-2 py-1 flex-1" accept="image/*" multiple>

            <button type="button" class="remove-color bg-red-400 text-white px-2 py-1 rounded text-xs"
                onclick="removeColorOption('${colorId}')">x</button>
        </div>
    `;

        $('#color-options-container').append(html);
    });

    $(document).on('click', '.remove-color', function () {
        $(this).closest('.color-option-row').remove();
    });
});
