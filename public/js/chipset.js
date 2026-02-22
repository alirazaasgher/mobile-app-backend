$(document).ready(function () {
    // Initialize clusterIndex based on existing rows (important for Edit pages)
    let clusterIndex = $(".cluster-row").length;

    $("#add-cluster").on("click", function () {
        const $container = $("#cpu-clusters-container");
        // Use :first to get a template, but ensure we clear it thoroughly
        const $newRow = $(".cluster-row:first").clone();

        // Update names and clear values
        $newRow.find("input").each(function () {
            let name = $(this).attr("name");
            if (name) {
                // Replaces the first number found in brackets [0] with the new index
                let newName = name.replace(
                    /\[(\d+)\]/,
                    "[" + clusterIndex + "]",
                );
                $(this).attr("name", newName).val("");
            }
        });

        // Remove any validation error classes or messages from the cloned row
        $newRow.find(".is-invalid").removeClass("is-invalid");
        $newRow.find(".invalid-feedback").remove();

        $container.append($newRow);
        clusterIndex++;
    });

    $(document).on("click", ".remove-cluster", function () {
        if ($(".cluster-row").length > 1) {
            $(this).closest(".cluster-row").remove();
        } else {
            // Instead of a harsh alert, just clear the values of the last remaining row
            $(".cluster-row:first").find("input").val("");
            alert(
                "You must have at least one CPU cluster. The fields have been cleared instead.",
            );
        }
    });

    let featureIndex = $(".feature-row").length;

    $("#add-feature").on("click", function () {
        const $container = $("#gpu-features-container");

        // Clone the first row as a template
        const $newRow = $(".feature-row:first").clone();

        // Update name with new index and clear the value
        $newRow
            .find("input")
            .attr("name", `gpu_features[${featureIndex}]`)
            .val("");

        // Cleanup any validation states from cloned row
        $newRow.find(".is-invalid").removeClass("is-invalid");

        $container.append($newRow);
        featureIndex++;
    });

    $(document).on("click", ".remove-feature", function () {
        if ($(".feature-row").length > 1) {
            $(this).closest(".feature-row").remove();
        } else {
            // Just clear the last remaining input instead of deleting the row
            $(".feature-row:first").find("input").val("");
        }
    });
    let cameraFeatureIndex = 1;

    // Add new camera feature row
    $("#add-camera-feature").on("click", function () {
        const $container = $(".camera-features");
        const $newRow = $(".feature-row:first").clone();

        $newRow.find("input").each(function () {
            $(this)
                .attr("name", `camera_features[${cameraFeatureIndex}]`)
                .val("");
        });

        $container.append($newRow);
        cameraFeatureIndex++;
    });

    // Remove camera feature row
    $(document).on("click", ".remove-feature", function () {
        if ($(".feature-row").length > 1) {
            $(this).closest(".feature-row").remove();
        } else {
            alert("At least one camera feature is required.");
        }
    });

    let locationFeatureIndex = $(".location-features .feature-row").length;

    // Add new feature
    $("#add-location-feature").on("click", function () {
        const newRow = `
            <div class="row g-2 feature-row mb-2">
                <div class="col-md-10">
                    <input type="text"
                           name="location_features[${locationFeatureIndex}]"
                           class="form-control"
                           placeholder="Enter location feature">
                </div>
                <div class="col-md-2">
                    <button type="button"
                            class="btn btn-danger btn-sm remove-feature">
                        Remove
                    </button>
                </div>
            </div>
        `;

        $(".location-features").append(newRow);
        locationFeatureIndex++;
    });

    // Remove feature
    $(document).on("click", ".remove-feature", function () {
        if ($(".location-features .feature-row").length > 1) {
            $(this).closest(".feature-row").remove();
        } else {
            alert("At least one location feature is required.");
        }
    });
});
