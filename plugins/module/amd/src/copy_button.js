async function copy(text) {
    if (navigator.clipboard) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
}

define([], function() {
    return {
        init: function(id) {
            ["ssh", "https", "checkout", "fetch-mr", "checkout-mr"].forEach((name) => {
                const prefix = `#code-dropdown-${id}`;
                const input = document.querySelector(`${prefix} .${name}-input`);
                const button = document.querySelector(`${prefix} .${name}-button`);
                const icon = document.querySelector(`${prefix} .${name}-button i`);
                if (!input || !button || !icon) {
                    return;
                }

                button.addEventListener("click", async () => {
                    await copy(input.value);
                    icon.classList.replace("fa-copy", "fa-check");
                    setTimeout(() => icon.classList.replace("fa-check", "fa-copy"), 500);
                });
            });
        },
    };
});
