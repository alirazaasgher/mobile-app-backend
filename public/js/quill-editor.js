$(document).ready(function () {
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
});
