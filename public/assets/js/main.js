document.addEventListener('DOMContentLoaded', function() {
    const cpfMasks = document.querySelectorAll('.cpf-mask');
    const cnpjMasks = document.querySelectorAll('.cnpj-mask');
    const phoneMasks = document.querySelectorAll('.phone-mask');
    const plateMasks = document.querySelectorAll('.plate-mask');

    cpfMasks.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length >= 3 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
            } else if (value.length >= 9 && value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            e.target.value = value;
        });
    });

    cnpjMasks.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.slice(0, 14);
            
            if (value.length >= 2 && value.length <= 5) {
                value = value.replace(/(\d{2})(\d)/, '$1.$2');
            } else if (value.length >= 5 && value.length <= 8) {
                value = value.replace(/(\d{2})(\d{3})(\d)/, '$1.$2.$3');
            } else if (value.length >= 8 && value.length <= 12) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d)/, '$1.$2.$3/$4');
            } else if (value.length >= 12 && value.length <= 14) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d)/, '$1.$2.$3/$4-$5');
            }
            e.target.value = value;
        });
    });

    phoneMasks.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length >= 2) {
                value = '(' + value;
            }
            if (value.length >= 4) {
                value = value.replace(/(\d{2})(\d)/, '$1) $2');
            }
            if (value.length >= 10) {
                value = value.replace(/(\d{2})(\d{5})(\d)/, '$1$2-$3');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{2})(\d{4})(\d)/, '$1$2-$3');
            }
            e.target.value = value;
        });
    });

    plateMasks.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 7) value = value.slice(0, 7);
            
            if (value.length >= 3) {
                value = value.replace(/([A-Z]{3})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
        
        input.addEventListener('blur', function(e) {
            const plate = e.target.value;
            const regex = /^[A-Z]{3}-\d{4}$/;
            if (!regex.test(plate) && plate.length > 0) {
                e.target.setCustomValidity('Placa inválida. Use o formato ABC-1234');
            } else {
                e.target.setCustomValidity('');
            }
        });
    });
});

function closeModal() {
    const modals = document.querySelectorAll('.fixed');
    modals.forEach(modal => {
        if (modal.id !== 'nav') {
            modal.classList.add('hidden');
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

window.closeModal = closeModal;