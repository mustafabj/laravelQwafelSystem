export default {
    init() {
        const cost = document.getElementById('cost');
        const paid = document.getElementById('paid');
        const rest = document.getElementById('costRest');

        const calc = () => {
            rest.value = Math.max(0, (+cost.value || 0) - (+paid.value || 0));
        };

        cost.addEventListener('input', calc);
        paid.addEventListener('input', calc);
    }
};
