    var picker_d = new Pikaday({
        field: document.getElementById('datepicker_desde'),
        format: 'DD/MM/YYYY',
        i18n: {
    previousMonth : 'Mes Anterior',
    nextMonth     : 'Mes Siguiente',
    months        : ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
    weekdays      : ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
    weekdaysShort : ['Dom','Lun','Mar','Mie','Jue','Vie','Sab']
}

    });
        var picker_h = new Pikaday({
        field: document.getElementById('datepicker_hasta'),
        format: 'DD/MM/YYYY',
        keyboardInput: false,
        i18n: {
    previousMonth : 'Mes Anterior',
    nextMonth     : 'Mes Siguiente',
    months        : ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
    weekdays      : ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
    weekdaysShort : ['Dom','Lun','Mar','Mie','Jue','Vie','Sab']
}

    });
