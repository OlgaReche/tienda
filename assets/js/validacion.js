/**
 * CAPA CLIENTE - JavaScript
 * 1) Valida los formularios con clase "validar" antes de enviarlos.
 *    Es solo una ayuda para el usuario: la validación que cuenta es la de PHP,
 *    porque el JavaScript se puede desactivar en el navegador.
 * 2) Pide confirmación antes de enviar formularios con data-confirmar (borrados).
 * 3) Envía automáticamente los formularios "auto-enviar" al cambiar la cantidad.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ---- 1. Validación de formularios ----
    document.querySelectorAll('form.validar').forEach(form => {
        form.addEventListener('submit', evento => {
            limpiarErrores(form);
            let valido = true;

            // Usa las reglas HTML5 de cada campo (required, type=email, min, pattern...)
            form.querySelectorAll('input, textarea, select').forEach(campo => {
                if (!campo.checkValidity()) {
                    mostrarError(campo, mensajeDe(campo));
                    valido = false;
                }
            });

            // Las dos contraseñas deben coincidir
            const pass = form.querySelector('[name="password"]');
            const pass2 = form.querySelector('[name="password2"]');
            if (pass && pass2 && pass.value !== pass2.value) {
                mostrarError(pass2, 'Las contraseñas no coinciden.');
                valido = false;
            }
            // Contraseña con letras y números (si se ha escrito)
            if (pass && pass2 && pass.value !== '' && !/^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(pass.value)) {
                mostrarError(pass, 'Mínimo 8 caracteres, con letras y números.');
                valido = false;
            }

            if (!valido) {
                evento.preventDefault();       // no se envía el formulario
                form.querySelector('.error-campo')?.previousElementSibling?.focus();
            }
        });
    });

    function mensajeDe(campo) {
        const v = campo.validity;
        if (v.valueMissing)  return 'Este campo es obligatorio.';
        if (v.typeMismatch)  return campo.type === 'email' ? 'Introduce un email válido.' : 'Formato no válido.';
        if (v.tooShort)      return `Mínimo ${campo.minLength} caracteres.`;
        if (v.patternMismatch) return 'El formato no es correcto.';
        if (v.rangeUnderflow) return `El valor mínimo es ${campo.min}.`;
        if (v.rangeOverflow)  return `El valor máximo es ${campo.max}.`;
        if (v.stepMismatch)   return 'Valor no válido.';
        return 'Revisa este campo.';
    }

    function mostrarError(campo, texto) {
        const span = document.createElement('span');
        span.className = 'error-campo js';
        span.textContent = texto;
        campo.insertAdjacentElement('afterend', span);
        campo.classList.add('campo-error');
    }

    function limpiarErrores(form) {
        form.querySelectorAll('.error-campo').forEach(s => s.remove());
        form.querySelectorAll('.campo-error').forEach(c => c.classList.remove('campo-error'));
    }

    // ---- 2. Confirmación de borrado ----
    document.querySelectorAll('form[data-confirmar]').forEach(form => {
        form.addEventListener('submit', evento => {
            if (!confirm(form.dataset.confirmar)) evento.preventDefault();
        });
    });

    // ---- 3. Cambiar cantidad en el carrito ----
    document.querySelectorAll('form.auto-enviar input[name="cantidad"]').forEach(input => {
        input.addEventListener('change', () => input.form.submit());
    });
});
