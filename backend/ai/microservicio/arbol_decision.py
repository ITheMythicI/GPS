"""
arbol_decision.py
-----------------
Árbol de decisión para clasificar contenedores BIN.

Entradas por contenedor (5 features):
  - volumen_pct      : float  (0–100) → calculado a partir de distanciaBoteTapa
  - humedad          : float  (0–100) → sensor DHT22
  - temperatura      : float  (°C)    → sensor DHT22
  - peso_kg          : float  (kg)    → sensor HX711
  - urgencia_area    : float  (0.0–1.0) → codificado desde ubicacion

Salida: 'alta' | 'media' | 'baja'

Basado en reglas fijas de negocio (no requiere datos históricos).
Puede re-entrenarse con datos reales en el futuro.
"""

import numpy as np
import joblib
import os
from sklearn.tree import DecisionTreeClassifier

# ── Ruta del modelo serializado ───────────────────────────────────────────────
MODEL_PATH = os.path.join(os.path.dirname(__file__), "modelo_arbol.joblib")

# ── Umbrales del sistema BIN ──────────────────────────────────────────────────
UMBRAL_VOLUMEN_ALTO  = 80.0   # % de llenado → prioridad alta
UMBRAL_VOLUMEN_MEDIO = 50.0   # % de llenado → prioridad media
UMBRAL_HUMEDAD_ALTO  = 85.0   # % humedad    → sube prioridad a alta
UMBRAL_TEMP_ALTO     = 40.0   # °C           → sube prioridad a alta
UMBRAL_PESO_ALTO     = 40.0   # kg           → prioridad alta

# ── Mapeo numérico de etiquetas ───────────────────────────────────────────────
LABEL_MAP     = {0: "baja", 1: "media", 2: "alta"}
LABEL_MAP_INV = {"baja": 0, "media": 1, "alta": 2}

# ── Codificación de ubicaciones ───────────────────────────────────────────────
# Factor de urgencia por área (0.0 = baja demanda, 1.0 = alta demanda).
# Un factor alto reduce el umbral de llenado requerido para prioridad Alta.
# Agregar nuevas áreas según crezca el sistema.
UBICACION_URGENCIA = {
    "área de sistemas":    1.0,   # Alta demanda → umbral más sensible
    "area de sistemas":    1.0,
    "área de química":     0.6,
    "area de quimica":     0.6,
    "área de electrónica": 0.7,
    "area de electronica": 0.7,
    "cafetería":           0.8,
    "cafeteria":           0.8,
    "estacionamiento":     0.3,
    # Ubicaciones no listadas → valor por defecto
}
UBICACION_DEFAULT = 0.5

# Número de features del modelo (se usa para detectar modelos obsoletos)
N_FEATURES = 5


def _codificar_ubicacion(ubicacion: str) -> float:
    """Convierte el nombre de ubicación a su factor de urgencia numérico."""
    return UBICACION_URGENCIA.get(str(ubicacion).lower().strip(), UBICACION_DEFAULT)


def _generar_datos_sinteticos(n: int = 4000):
    """
    Genera datos sintéticos basados en reglas fijas del sistema BIN.
    Incluye ubicacion_urgencia como quinta feature.

    Reglas de clasificación:
      Alta  : volumen ≥ 80%  ó  humedad ≥ 85%  ó  temp ≥ 40°C  ó  peso ≥ 40 kg
               ó  (volumen ≥ 60% Y urgencia_area ≥ 0.8)  ← área crítica semillena
      Media : volumen ≥ 50%  (sin ninguna condición de alta)
      Baja  : resto
    """
    rng = np.random.default_rng(42)

    volumen  = rng.uniform(0, 100, n)
    humedad  = rng.uniform(20, 100, n)
    temp     = rng.uniform(10, 55, n)
    peso     = rng.uniform(0, 80, n)
    urgencia = rng.choice(
        list(UBICACION_URGENCIA.values()) + [UBICACION_DEFAULT], size=n
    )

    cond_alta = (
        (volumen >= UMBRAL_VOLUMEN_ALTO) |
        (humedad >= UMBRAL_HUMEDAD_ALTO) |
        (temp    >= UMBRAL_TEMP_ALTO)    |
        (peso    >= UMBRAL_PESO_ALTO)    |
        ((volumen >= 60.0) & (urgencia >= 0.8))  # área de alta demanda + medio lleno
    )

    labels = np.where(
        cond_alta,
        LABEL_MAP_INV["alta"],
        np.where(
            volumen >= UMBRAL_VOLUMEN_MEDIO,
            LABEL_MAP_INV["media"],
            LABEL_MAP_INV["baja"]
        )
    )

    X = np.column_stack([volumen, humedad, temp, peso, urgencia])
    return X, labels


def entrenar_y_guardar():
    """Entrena el árbol con datos sintéticos y lo serializa en disco."""
    X, y = _generar_datos_sinteticos()
    clf = DecisionTreeClassifier(max_depth=7, random_state=42)
    clf.fit(X, y)
    joblib.dump(clf, MODEL_PATH)
    print(f"[arbol_decision] Modelo guardado en: {MODEL_PATH} (features={N_FEATURES})")
    return clf


def cargar_modelo() -> DecisionTreeClassifier:
    """
    Carga el modelo desde disco.
    Si no existe o tiene distinto número de features, re-entrena automáticamente.
    """
    if os.path.exists(MODEL_PATH):
        clf = joblib.load(MODEL_PATH)
        if hasattr(clf, "n_features_in_") and clf.n_features_in_ != N_FEATURES:
            print(f"[arbol_decision] Modelo obsoleto ({clf.n_features_in_} features "
                  f"vs {N_FEATURES} esperadas). Re-entrenando...")
            return entrenar_y_guardar()
        return clf

    print("[arbol_decision] Modelo no encontrado, entrenando por primera vez...")
    return entrenar_y_guardar()


def clasificar_contenedores(contenedores: list) -> list:
    """
    Clasifica una lista de contenedores usando el árbol de decisión.

    Parámetros
    ----------
    contenedores : list[dict]
        Cada dict debe tener:
          id_contenedor, volumen_pct, humedad, temperatura, peso_kg, ubicacion

    Retorna
    -------
    list[dict] con campos originales + prioridad + score + urgencia_area
    """
    clf = cargar_modelo()

    resultados = []
    for c in contenedores:
        vol      = float(c.get("volumen_pct",  0))
        hum      = float(c.get("humedad",      0))
        temp     = float(c.get("temperatura",  0))
        peso     = float(c.get("peso_kg",      0))
        urgencia = _codificar_ubicacion(c.get("ubicacion", ""))

        X      = np.array([[vol, hum, temp, peso, urgencia]])
        pred   = clf.predict(X)[0]
        probas = clf.predict_proba(X)[0]
        score  = float(round(probas[pred], 4))

        resultados.append({
            **c,
            "prioridad":     LABEL_MAP[pred],
            "score":         score,
            "volumen_pct":   vol,
            "urgencia_area": urgencia,
        })

    return resultados
