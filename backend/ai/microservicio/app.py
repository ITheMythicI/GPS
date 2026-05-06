"""
app.py — Microservicio Flask BIN-AI
-------------------------------------
Expone dos endpoints REST en localhost:5000.
Solo debe ser accesible desde la misma VM (llamado por PHP via cURL).

Endpoints:
  POST /clasificar  →  árbol de decisión
  POST /rutas       →  Dijkstra / Nearest Neighbor
  GET  /health      →  healthcheck
"""

from flask import Flask, request, jsonify
from arbol_decision import clasificar_contenedores
from dijkstra import calcular_ruta

app = Flask(__name__)


# ── Healthcheck ───────────────────────────────────────────────────────────────

@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "servicio": "BIN-AI"}), 200


# ── Árbol de Decisión ─────────────────────────────────────────────────────────

@app.route("/clasificar", methods=["POST"])
def clasificar():
    """
    Body JSON esperado:
    {
        "contenedores": [
            {
                "id_contenedor": 1,
                "volumen_pct":   75.5,
                "humedad":       60.0,
                "temperatura":   28.3,
                "peso_kg":       18.0,
                "latitud":       25.5334,
                "longitud":      -103.4358,
                "ubicacion":     "Área de Sistemas"
            },
            ...
        ]
    }

    Respuesta:
    {
        "status": "ok",
        "resultados": [
            { ...campos_originales, "prioridad": "alta", "score": 0.97 },
            ...
        ]
    }
    """
    data = request.get_json(force=True, silent=True)
    if not data or "contenedores" not in data:
        return jsonify({"status": "error", "message": "Falta el campo 'contenedores'"}), 400

    contenedores = data["contenedores"]
    if not isinstance(contenedores, list) or len(contenedores) == 0:
        return jsonify({"status": "error", "message": "La lista de contenedores está vacía"}), 400

    try:
        resultados = clasificar_contenedores(contenedores)
        return jsonify({"status": "ok", "resultados": resultados}), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


# ── Dijkstra / Ruta Óptima ────────────────────────────────────────────────────

@app.route("/rutas", methods=["POST"])
def rutas():
    """
    Body JSON esperado:
    {
        "contenedores": [
            {
                "id_contenedor": 1,
                "latitud":       25.5334,
                "longitud":      -103.4358,
                "ubicacion":     "Área de Sistemas",
                "prioridad":     "alta"
            },
            ...
        ]
    }

    Respuesta:
    {
        "status": "ok",
        "ruta_ordenada": [ {...contenedor}, ... ],
        "distancia_km": 0.23,
        "coordenadas": [[lat, lon], ...]
    }
    """
    data = request.get_json(force=True, silent=True)
    if not data or "contenedores" not in data:
        return jsonify({"status": "error", "message": "Falta el campo 'contenedores'"}), 400

    contenedores = data["contenedores"]
    if not isinstance(contenedores, list):
        return jsonify({"status": "error", "message": "Formato inválido"}), 400

    try:
        resultado = calcular_ruta(contenedores)
        return jsonify({"status": "ok", **resultado}), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


# ── Main ──────────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    # Solo escucha en localhost para que no sea accesible desde internet
    app.run(host="127.0.0.1", port=5000, debug=False)
