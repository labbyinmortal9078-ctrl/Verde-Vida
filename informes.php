<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ========== CONSULTAS PARA LOS GRÁFICOS ==========

// 1. Plantas registradas en el último mes (por día)
$query_plantas_mes = "SELECT DATE(hora) as fecha, COUNT(*) as total 
                        FROM especies 
                        WHERE hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY DATE(hora) 
                        ORDER BY fecha ASC";
$result_plantas_mes = $conex->query($query_plantas_mes);
$fechas_plantas = [];
$totales_plantas = [];
while($row = $result_plantas_mes->fetch_assoc()) {
    $fechas_plantas[] = $row['fecha'];
    $totales_plantas[] = $row['total'];
}

// Total de plantas en el último mes
$total_plantas_mes = array_sum($totales_plantas);

// 2. Valor total del inventario en pesos
$query_valor = "SELECT SUM(precio_venta * cantidad_disponible) as valor_total,
                       SUM(precio_costo * cantidad_disponible) as costo_total
                FROM inventario";
$result_valor = $conex->query($query_valor);
$valor = $result_valor->fetch_assoc();
$valor_total = $valor['valor_total'] ?? 0;
$costo_total = $valor['costo_total'] ?? 0;
$ganancia_potencial = $valor_total - $costo_total;

// 3. Usuarios registrados (por mes)
$query_usuarios = "SELECT DATE_FORMAT(fecha_contratacion, '%Y-%m') as mes, COUNT(*) as total 
                    FROM usuarios 
                    WHERE fecha_contratacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(fecha_contratacion, '%Y-%m')
                    ORDER BY mes ASC";
$result_usuarios = $conex->query($query_usuarios);
$meses_usuarios = [];
$totales_usuarios = [];
while($row = $result_usuarios->fetch_assoc()) {
    $meses_usuarios[] = $row['mes'];
    $totales_usuarios[] = $row['total'];
}

$total_usuarios = array_sum($totales_usuarios);

// 4. Plantas por tipo (para gráfico adicional)
$query_tipos = "SELECT tipo_planta, COUNT(*) as total 
                FROM especies 
                GROUP BY tipo_planta";
$result_tipos = $conex->query($query_tipos);
$tipos_labels = [];
$tipos_data = [];
while($row = $result_tipos->fetch_assoc()) {
    $tipos_labels[] = ucfirst($row['tipo_planta']);
    $tipos_data[] = $row['total'];
}

// 5. Plantas por dificultad
$query_dificultad = "SELECT dificultad_cultivo, COUNT(*) as total 
                        FROM especies 
                        GROUP BY dificultad_cultivo";
$result_dificultad = $conex->query($query_dificultad);
$dificultad_labels = [];
$dificultad_data = [];
while($row = $result_dificultad->fetch_assoc()) {
    $dificultad_labels[] = ucfirst($row['dificultad_cultivo']);
    $dificultad_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes y Gráficos - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #1a5f4b 0%, #0d3b2e 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            background: rgba(255,255,255,0.15);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.3);
        }

        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #1a3e30;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 2rem;
        }

        .header p {
            color: #666;
            margin-top: 0.5rem;
        }

        /* Tarjetas de estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-icon.green { background: #d4edda; }
        .stat-icon.blue { background: #cce5ff; }
        .stat-icon.yellow { background: #fff3cd; }
        .stat-icon.purple { background: #e2d4f0; }

        .stat-info h3 {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-info .number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a3e30;
        }

        /* Grid de gráficos */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .chart-card h3 {
            color: #1a3e30;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f9f4;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .chart-full {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            .main-container {
                padding: 0 1rem;
            }
            .header h1 {
                font-size: 1.5rem;
            }
            .nav-links {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="main.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>🌿 Verde Vida</span>
        </a>
        <div class="nav-links">
            <a href="main.php" class="nav-link">🏠 Inicio</a>
            <a href="ver_plantas.php" class="nav-link">🌱 Plantas</a>
            <a href="ver_auditoria.php" class="nav-link">📋 Auditoría</a>
            <a href="logout.php" class="nav-link">🚪 Salir</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;gap: 1rem;">
            <div>
            <h1>📊 Informes y Gráficos</h1>
            <p>Estadísticas y reportes del sistema Verde Vida</p>
        </div>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="exportacion_pdf.php" class="btn-export-pdf">
                <i class="fas fa-file-pdf"></i> Exportar a PDF
            </a>
            <a href="exportacion_informes_excel.php" class="btn-export-excel">
                <i class="fas fa-file-excel"></i> Exportar a Excel  
            </a>
        </div>
    </div>

        <!-- Tarjetas de estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">🌱</div>
                <div class="stat-info">
                    <h3>Plantas (último mes)</h3>
                    <div class="number"><?php echo $total_plantas_mes; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">💰</div>
                <div class="stat-info">
                    <h3>Valor Inventario</h3>
                    <div class="number">$<?php echo number_format($valor_total, 2); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon yellow">👥</div>
                <div class="stat-info">
                    <h3>Usuarios Registrados</h3>
                    <div class="number"><?php echo $total_usuarios; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">📈</div>
                <div class="stat-info">
                    <h3>Ganancia Potencial</h3>
                    <div class="number">$<?php echo number_format($ganancia_potencial, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <!-- Gráfico 1: Plantas registradas en el último mes -->
            <div class="chart-card">
                <h3>🌱 Plantas registradas en el último mes</h3>
                <div class="chart-container">
                    <canvas id="chartPlantasMes"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Usuarios registrados por mes -->
            <div class="chart-card">
                <h3>👥 Usuarios registrados por mes</h3>
                <div class="chart-container">
                    <canvas id="chartUsuarios"></canvas>
                </div>
            </div>

            <!-- Gráfico 3: Distribución de plantas por tipo -->
            <div class="chart-card">
                <h3>🌿 Plantas por tipo</h3>
                <div class="chart-container">
                    <canvas id="chartTipos"></canvas>
                </div>
            </div>

            <!-- Gráfico 4: Plantas por dificultad -->
            <div class="chart-card">
                <h3>⭐ Dificultad de cultivo</h3>
                <div class="chart-container">
                    <canvas id="chartDificultad"></canvas>
                </div>
            </div>

            <!-- Gráfico 5: Valor del inventario (comparación) -->
            <div class="chart-card chart-full">
                <h3>💰 Comparación de valores del inventario</h3>
                <div class="chart-container" style="height: 250px;">
                    <canvas id="chartValores"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ========== GRÁFICO 1: PLANTAS REGISTRADAS EN EL ÚLTIMO MES ==========
        const ctx1 = document.getElementById('chartPlantasMes').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($fechas_plantas); ?>,
                datasets: [{
                    label: 'Plantas registradas',
                    data: <?php echo json_encode($totales_plantas); ?>,
                    borderColor: '#2d8f6e',
                    backgroundColor: 'rgba(45, 143, 110, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2d8f6e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // ========== GRÁFICO 2: USUARIOS REGISTRADOS POR MES ==========
        const ctx2 = document.getElementById('chartUsuarios').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($meses_usuarios); ?>,
                datasets: [{
                    label: 'Usuarios registrados',
                    data: <?php echo json_encode($totales_usuarios); ?>,
                    backgroundColor: [
                        'rgba(45, 143, 110, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(139, 195, 74, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(233, 30, 99, 0.8)'
                    ],
                    borderColor: [
                        '#2d8f6e',
                        '#4caf50',
                        '#8bc34a',
                        '#ffc107',
                        '#ff9800',
                        '#e91e63'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // ========== GRÁFICO 3: PLANTAS POR TIPO (DOUGHNUT) ==========
        const ctx3 = document.getElementById('chartTipos').getContext('2d');
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($tipos_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($tipos_data); ?>,
                    backgroundColor: [
                        '#2d8f6e',
                        '#4caf50',
                        '#8bc34a',
                        '#ffc107',
                        '#ff9800',
                        '#e91e63',
                        '#9c27b0',
                        '#3f51b5'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // ========== GRÁFICO 4: DIFICULTAD DE CULTIVO (PIE) ==========
        const ctx4 = document.getElementById('chartDificultad').getContext('2d');
        new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($dificultad_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dificultad_data); ?>,
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // ========== GRÁFICO 5: COMPARACIÓN DE VALORES ==========
        const ctx5 = document.getElementById('chartValores').getContext('2d');
        new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: ['Costo Total', 'Valor de Venta', 'Ganancia Potencial'],
                datasets: [{
                    label: 'Pesos ($)',
                    data: [
                        <?php echo $costo_total; ?>,
                        <?php echo $valor_total; ?>,
                        <?php echo $ganancia_potencial; ?>
                    ],
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(45, 143, 110, 0.8)',
                        'rgba(40, 167, 69, 0.8)'
                    ],
                    borderColor: [
                        '#dc3545',
                        '#2d8f6e',
                        '#28a745'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>