import { Dropdown } from "bootstrap";

document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de inicio cargada');
    
    // Activar dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new Dropdown(dropdownToggleEl);
    });
});