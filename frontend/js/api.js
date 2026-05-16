/**
 * api.js - Centraliza todas las llamadas al Backend vía Proxy
 */

const API = {
    async fetch(action, body = {}) {
        let sendBody;

        // Si body ya es un FormData, usarlo directamente
        if (body instanceof FormData) {
            sendBody = body;
            sendBody.append('action', action);
        } else {
            // Si es un objeto plano, crear un nuevo FormData
            sendBody = new FormData();
            sendBody.append('action', action);
            for (const key in body) {
                sendBody.append(key, body[key]);
            }
        }

        try {
            const response = await fetch('api/ia_proxy.php?action=' + action, {
                method: 'POST',
                body: sendBody
            });
            return await response.json();
        } catch (error) {
            console.error('[API Error]:', error);
            return { status: 'error', message: error.message };
        }
    },

    async generarReporte() {
        return this.fetch('reporte');
    },

    async clasificar() {
        return this.fetch('clasificar');
    },

    async obtenerRutas() {
        return this.fetch('rutas');
    },

    async obtenerContenedores() {
        return this.fetch('contenedores');
    },

    async simular() {
        return this.fetch('simular');
    },

    async obtenerZonas() {
        return this.fetch('zonas');
    },
    
    async guardarZona(datos) {
        return this.fetch('guardar_zona', datos);
    },

    async guardarContenedor(datos) {
        return this.fetch('guardar_contenedor', datos);
    },

    async borrarZona(id) {
        return this.fetch('borrar_zona', { id_zona: id });
    },

    async borrarContenedor(id) {
        return this.fetch('borrar_contenedor', { id_contenedor: id });
    },

    async enviarReporteIncidencia(formData) {
        return this.fetch('crear_reporte', formData);
    },

    async obtenerReportes() {
        return this.fetch('obtener_reportes', {}, 'GET');
    },

    async obtenerActividad() {
        return this.fetch('obtener_actividad', {}, 'GET');
    },

    async obtenerAjustes() {
        return this.fetch('obtener_ajustes', {}, 'GET');
    },

    async guardarAjustes(datos) {
        return this.fetch('guardar_ajustes', datos);
    },

    async subirFotoPerfil(formData) {
        return this.fetch('subir_foto_perfil', formData);
    }
};








