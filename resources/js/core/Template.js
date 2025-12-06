// resources/js/core/Template.js

export const TemplateManager = {
    cache: {},

    /**
     * Load template content by ID from <template id="...">
     * Returns a fresh DocumentFragment each time.
     */
    load(id) {
        if (this.cache[id]) {
            return this.cache[id].content.cloneNode(true);
        }

        const tpl = document.getElementById(id);

        if (!tpl) {
            console.error(`[Template] Template not found: #${id}`);
            return document.createDocumentFragment();
        }

        this.cache[id] = tpl;
        return tpl.content.cloneNode(true);
    },

    /**
     * Render a template directly into a container
     */
    render(id, container) {
        const dom = this.load(id);
        container.innerHTML = "";
        container.appendChild(dom);
    },

    /**
     * Clone template and return the first element inside it
     * (useful for rows/items)
     */
    cloneRoot(id) {
        const fragment = this.load(id);
        return fragment.firstElementChild
            ? fragment.firstElementChild
            : null;
    },

    /**
     * Append a template fragment into a container
     */
    append(id, container) {
        const dom = this.load(id);
        container.appendChild(dom);
    }
};

export default TemplateManager;
