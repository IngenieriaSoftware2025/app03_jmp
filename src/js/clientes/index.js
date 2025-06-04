import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../funciones";


const FormClientes = document.getElementById('FormClientes');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const validarTelefono = document.getElementById('telefono');
const validarnit = document.getElementById('nit');

const validacionTelefono = () => {
    const cantidadDigitos = validarTelefono.value;

    if (cantidadDigitos.lenght < 1) {
        validarTelefono.classList.remove('is_valid', 'is_invalid');
    } else {
        if (cantidadDigitos.lenght != 8) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Datos Erroneos",
                text: "Ingrese exactamente 8 digitos",
                timer: 3000
            });
        } else {
            
        }
        
    }
}