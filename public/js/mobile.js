let colorCounter = 1;
$(document).ready(function () {
    document
        .getElementById("variants-wrapper")
        .addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-variant")) {
                e.target.closest(".variant-row").remove();
            }
        });

    $(document).on("click", "#addColorBtn", function () {
        colorCounter++;
        const colorId = `color_${colorCounter}`;
        const colorSlug = colorId.toLowerCase();
        const html = `
            <div class="border rounded p-3 mb-2" id="${colorId}">
            <div class="row align-items-center g-2">

                <!-- Color name -->
                <div class="col-md-4">
                <input type="text"
                    name="variants[color_names][${colorSlug}]"
                    class="form-control form-control-sm"
                    placeholder="Color name">
                </div>

                <!-- Hex code -->
                <div class="col-md-3">
                <input type="text"
                    name="variants[color_hex][${colorSlug}]"
                    value="#000000"
                    class="form-control form-control-sm"
                    oninput="updateColorCircle('${colorId}', this.value)">
                </div>

                <!-- Images -->
                <div class="col-md-4">
                <input type="file"
                    name="variants[color_image][${colorSlug}][]"
                    class="form-control form-control-sm"
                    accept="image/*"
                    multiple>
                </div>

                <!-- Remove -->
                <div class="col-md-1 text-center">
                <button type="button"
                    class="remove-color btn btn-danger btn-sm"
                    data-target="${colorId}">
                    &times;
                </button>
                </div>

            </div>
            </div>
            `;

        $("#color-options-container").append(html);
    });
    $('select[name="competitors[]"]').select2({
        placeholder: "Select Status",
        width: "100%",
    });

    $(".quill-editor").each(function () {
        const editor = this;
        const targetId = $(editor).data("target"); // Get the target textarea ID
        const textarea = $("#" + targetId)[0]; // Find textarea by ID

        const quill = new Quill(editor, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ font: [] }, { size: [] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ color: [] }, { background: [] }],
                    [{ script: "sub" }, { script: "super" }],
                    [{ header: 1 }, { header: 2 }, "blockquote", "code-block"],
                    [
                        { list: "ordered" },
                        { list: "bullet" },
                        { indent: "-1" },
                        { indent: "+1" },
                    ],
                    [{ direction: "rtl" }, { align: [] }],
                    ["link", "image", "video"],
                    ["clean"],
                ],
            },
        });

        // Load existing value
        if (textarea && textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        // Sync HTML back to textarea
        quill.on("text-change", function () {
            textarea.value = quill.root.innerHTML;
        });
    });

    $(document).on('click', '.remove-color', function () {
        const targetId = $(this).data('target');
        const $row = $('#' + targetId);

        if (!$row.length) return;

        if (!confirm('Remove this color option?')) return;

        $row.remove();
    });
});
