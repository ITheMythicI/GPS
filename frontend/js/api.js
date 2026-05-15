/**
 * api.js - Centraliza todas las llamadas al Backend vía Proxy
 */

const API = {
    async fetch(action, body = {}) {
        const formData = new FormData();
        formData.append('action', action);
        for (const key in body) {
            formData.append(key, body[key]);
        }

        try {
            const response = await fetch('api/ia_proxy.php?action=' + action, {
                method: 'POST',
                body: formData
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
    }
};


