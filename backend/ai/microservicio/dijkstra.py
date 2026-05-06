"""
dijkstra.py
-----------
Calcula la ruta óptima de recolección para contenedores prioritarios
usando el algoritmo de Dijkstra sobre un grafo completo de distancias.

El grafo es dinámico: se construye a partir de las coordenadas lat/lon
de los contenedores que el backend PHP envía en cada petición.

Estrategia:
  - Nodo inicial: primero de la lista (punto de partida del camión)
  - Grafo: completo (cada contenedor conectado con todos los demás)
  - Peso de aristas: distancia Haversine en km
  - Resultado: orden óptimo de visita (Nearest Neighbor como heurística
    rápida; Dijkstra para path entre nodos intermedios si se expande).
"""

import heapq
import math


def _haversine(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    """Distancia en km entre dos puntos geográficos."""
    R = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi  = math.radians(lat2 - lat1)
    dlam  = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2) ** 2 + math.cos(phi1) * math.cos(phi2) * math.sin(dlam / 2) ** 2
    return R * 2 * math.asin(math.sqrt(a))


def _construir_grafo(contenedores: list) -> dict:
    """
    Construye un grafo completo con distancias Haversine.
    Retorna: {idx: {idx_vecino: distancia_km}}
    """
    n = len(contenedores)
    grafo = {i: {} for i in range(n)}
    for i in range(n):
        for j in range(n):
            if i != j:
                d = _haversine(
                    float(contenedores[i]["latitud"]),
                    float(contenedores[i]["longitud"]),
                    float(contenedores[j]["latitud"]),
                    float(contenedores[j]["longitud"]),
                )
                grafo[i][j] = d
    return grafo


def _dijkstra(grafo: dict, origen: int) -> tuple:
    """
    Dijkstra estándar desde un nodo origen.
    Retorna: (distancias, predecesores)
    """
    dist = {nodo: math.inf for nodo in grafo}
    dist[origen] = 0
    pred = {nodo: None for nodo in grafo}
    heap = [(0.0, origen)]

    while heap:
        d_actual, u = heapq.heappop(heap)
        if d_actual > dist[u]:
            continue
        for v, peso in grafo[u].items():
            alt = dist[u] + peso
            if alt < dist[v]:
                dist[v] = alt
                pred[v] = u
                heapq.heappush(heap, (alt, v))

    return dist, pred


def _nearest_neighbor(grafo: dict, inicio: int) -> list:
    """
    Heurística Nearest Neighbor para TSP aproximado.
    Construye una ruta visitando el nodo no visitado más cercano.
    """
    n = len(grafo)
    visitados = set()
    ruta = [inicio]
    visitados.add(inicio)
    actual = inicio

    while len(visitados) < n:
        vecinos = [(grafo[actual][j], j) for j in grafo[actual] if j not in visitados]
        if not vecinos:
            break
        _, siguiente = min(vecinos)
        ruta.append(siguiente)
        visitados.add(siguiente)
        actual = siguiente

    return ruta


def calcular_ruta(contenedores: list) -> dict:
    """
    Calcula la ruta óptima de recolección.

    Parámetros
    ----------
    contenedores : list[dict]
        Cada dict debe tener: id_contenedor, latitud, longitud
        (y opcionalmente prioridad, ubicacion)

    Retorna
    -------
    dict con:
      - ruta_ordenada  : list[dict] con los contenedores en orden de visita
      - distancia_km   : float con la distancia total estimada
      - coordenadas    : list[[lat, lon]] para dibujar el L.polyline en Leaflet
    """
    if not contenedores:
        return {"ruta_ordenada": [], "distancia_km": 0, "coordenadas": []}

    if len(contenedores) == 1:
        c = contenedores[0]
        return {
            "ruta_ordenada": [c],
            "distancia_km": 0,
            "coordenadas": [[float(c["latitud"]), float(c["longitud"])]],
        }

    grafo = _construir_grafo(contenedores)
    orden_indices = _nearest_neighbor(grafo, inicio=0)

    # Calcular distancia total
    distancia_total = 0.0
    for k in range(len(orden_indices) - 1):
        i, j = orden_indices[k], orden_indices[k + 1]
        distancia_total += grafo[i][j]

    ruta_ordenada = [contenedores[i] for i in orden_indices]
    coordenadas   = [
        [float(c["latitud"]), float(c["longitud"])]
        for c in ruta_ordenada
    ]

    return {
        "ruta_ordenada": ruta_ordenada,
        "distancia_km":  round(distancia_total, 4),
        "coordenadas":   coordenadas,
    }
