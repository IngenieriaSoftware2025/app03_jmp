import { Dropdown } from "bootstrap"; //si utilizo dropdown en mi layaut  (Dropdown es un funcion interna del MVC)
import Swal from "sweetalert2"; //para utilizar las alertas
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones"; //(validarFormulario es un funcion interna del MVC)
import { lenguaje } from "../lenguaje"; //(lenguaje es un funcion interna del MVC)

const FormClientes = document.getElementById('FormClientes');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnEliminar = document.getElementById('BtnEliminar');
const validarTelefono = document.getElementById('telefono');
const validarNit = document.getElementById('nit');

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
            validarTelefono.classList.remove('is_valid');
            validarTelefono.classList.add('is_invalid');
        } else {
            validarTelefono.classList.add('is_valid');
            validarTelefono.classList.remove('is_invalid');
        }

    }
}


const guardarCliente = async (event) =>{
    event.preventDefault(); //evita el envio del formulario
    BtnGuardar.ariaDisabled = true;

    if (!validarFormulario(FormClientes, ['cliente_id'])) {
         Swal.fire({
            position: "center",
            icon: "warning",
            title: "FORMULARIO INCOMPLETO",
            text: "Debe de validar todos los campos",
            timer: 3000
        });
    }

    //crea una instancia de la clase FormData
    const body = new FormData (FormClientes);
    const url = '/app03_jmp/clientes/guardarCliente'
    const config = {
        method: 'POST',
        body
        //TRATAREMOS DE GAURDAR UN CLIENTE
        
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();

        const {codigo, mensaje} = datos;

        if (codigo === 1) {
            Swal.fire({
            position: "top-end",
            icon: "success",
            title: "Aprovado",
            text: mensaje,
            timer: 3000
        });

        limpiarFormulario();
        buscarCliente();
            
        } else {
            
        }





    } catch (error) {
        console.log(error)
                
    }
    BtnGuardar.disabled= false;
}




function validarNit() {
    const nit = nit.value.trim();

    let nd, add = 0;

    if (nd = /^(\d+)-?([\dkK])$/.exec(nit)) {
        nd[2] = (nd[2].toLowerCase() === 'k') ? 10 : parseInt(nd[2], 10);

        for (let i = 0; i < nd[1].length; i++) {
            add += ((((i - nd[1].length) * -1) + 1) * parseInt(nd[1][i], 10));
        }
        return ((11 - (add % 11)) % 11) === nd[2];
    } else {
        return false;
    }
}

const EsValidoNit = () => {

    validarNit();

    if (validarNit()) {
        nit.classList.add('is-valid');
        nit.classList.remove('is-invalid');
    } else {
        nit.classList.remove('is-valid');
        nit.classList.add('is-invalid');

        Swal.fire({
            position: "center",
            icon: "error",
            title: "NIT INVALIDO",
            text: "El numero de nit ingresado es invalido",
            showConfirmButton: true,
        });

    }
}

const dataTable = new DataTable('#TableClientes', {
    dom: `
        <"row mt-3 justify-content-between" 
            <"col" l> 
            <"col" B> 
            <"col-3" f>
        >
        t
        <"row mt-3 justify-content-between" 
            <"col-md-3 d-flex align-items-center" i> 
            <"col-md-8 d-flex justify-content-end" p>
        >
    `,

    languaje: lenguaje,
    data: [],
    columns: [
        {
            title: 'N°',
            data: 'cliente_id',
            width: '%',
            render: (data, type, row, meta) => meta.row + 1
        },
        { title: 'Nombre', data: 'nombres' },
        { title: 'Apellido', data: 'apellidos' },
        { title: 'NIT', data: 'nit' },
        { title: 'Telefono', data: 'telefono' },
        { title: 'Correo', data: 'correo' },
        {
            title: 'Opciones',
            data: 'cliente_id',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                return
                `<div class='d-flex justify-content-center'>
                    <button class='btn btn-warning modificar mx-1' 
                        data-id="${data}" 
                        data-nombre="${row.nombres}"  
                        data-apellidos="${row.apellidos}"
                        data-telefono="${row.telefono}"  
                        data-sar="${row.nit}"   
                        data-correo="${row.correo}"  
                        <i class='bi bi-pencil-square me-1'></i> Modificar
                    </button>
                    <button class='btn btn-danger eliminar mx-1' 
                        data-id="${data}">
                        <i class="bi bi-trash3 me-1"></i>Eliminar
                    </button>
                </div>
                `;
            }
        }
    ],
});


//EVENTO
//Eventos

validarTelefono.addEventListener('change', validacionTelefono);
validarNit.addEventListener('change', EsValidoNit);

//guardar
FormClientes.addEventListener('submit', guardarCliente)