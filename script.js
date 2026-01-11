document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const semesterBtns = document.querySelectorAll('.semester-btn');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const materialCards = document.querySelectorAll('.material-card');

    let currentSemester = 'all';
    let currentType = 'all';
    let searchTerm = '';

    function filterMaterials() {
        materialCards.forEach(card => {
            const type = card.getAttribute('data-type');
            const semester = card.getAttribute('data-semester');
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p').textContent.toLowerCase();

            const matchesType = currentType === 'all' || type === currentType;
            const matchesSemester = currentSemester === 'all' || semester === currentSemester;
            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);

            if (matchesType && matchesSemester && matchesSearch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', (e) => {
        searchTerm = e.target.value.toLowerCase();
        filterMaterials();
    });

    semesterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            semesterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentSemester = btn.getAttribute('data-semester');
            filterMaterials();
        });
    });

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentType = btn.getAttribute('data-filter');
            filterMaterials();
        });
    });
});
