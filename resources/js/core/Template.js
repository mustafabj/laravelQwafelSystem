const Template = {
    get(id) {
        const tpl = document.getElementById(id);
        if (!tpl) {
            console.error(`[Template] Missing template: ${id}`);
            return null;
        }
        return tpl;
    },

    clone(id) {
        const tpl = this.get(id);
        return tpl ? tpl.content.firstElementChild.cloneNode(true) : null;
    },

    fill(el, data = {}) {
        Object.entries(data).forEach(([key, value]) => {
            const target = el.querySelector(`[data-bind="${key}"]`);
            if (target) {
                target.textContent = value;
            }
        });
        return el;
    }
};

export default Template;
