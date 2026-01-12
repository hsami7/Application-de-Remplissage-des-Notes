<?php
// views/professor/statistics.php
authorize_user(['professeur']);

require_once __DIR__ . '/../../core/actions/prof_actions.php';

$prof_id = $_SESSION['user_id'];
$statistics_data = get_professor_statistics_data($prof_id);

?>

<div class="page-container">
    <h2>Statistiques des Notes</h2>

    <?php if (empty($statistics_data)): ?>
        <div class="message info">
            Aucune donnée statistique disponible pour vos matières ou périodes actuelles.
        </div>
    <?php else: ?>
        <?php foreach ($statistics_data as $subject_stats): ?>
            <div class="stats-section">
                <h3><?php echo htmlspecialchars($subject_stats['matiere_info']['matiere_nom']); ?> (<?php echo htmlspecialchars($subject_stats['matiere_info']['filiere_nom']); ?> - <?php echo htmlspecialchars($subject_stats['matiere_info']['periode_nom']); ?> <?php echo htmlspecialchars($subject_stats['matiere_info']['annee_universitaire']); ?>)</h3>

                <?php if (isset($subject_stats['error'])): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($subject_stats['error']); ?>
                    </div>
                <?php else: ?>
                    <div class="stats-overview">
                        <h4>Statistiques Globales</h4>
                        <ul>
                            <li>Moyenne Générale: <strong><?php echo $subject_stats['statistiques_globales']['moyenne_generale'] ?? 'N/A'; ?></strong></li>
                            <li>Médiane: <strong><?php echo $subject_stats['statistiques_globales']['mediane'] ?? 'N/A'; ?></strong></li>
                            <li>Note Minimale: <strong><?php echo $subject_stats['statistiques_globales']['note_min'] ?? 'N/A'; ?></strong></li>
                            <li>Note Maximale: <strong><?php echo $subject_stats['statistiques_globales']['note_max'] ?? 'N/A'; ?></strong></li>
                            <li>Écart Type: <strong><?php echo $subject_stats['statistiques_globales']['ecart_type'] ?? 'N/A'; ?></strong></li>
                        </ul>
                    </div>

                    <div class="stats-distribution">
                        <h4>Distribution des Notes</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Intervalle</th>
                                    <th>Nombre d'étudiants</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subject_stats['distribution_notes'] as $range => $count): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($range); ?></td>
                                        <td><?php echo htmlspecialchars($count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="stats-details">
                        <h4>Moyennes Détaillées par Étudiant</h4>
                        <?php if (empty($subject_stats['moyennes_etudiants'])): ?>
                            <p>Aucune moyenne calculée pour les étudiants inscrits.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom Étudiant</th>
                                        <th>Moyenne</th>
                                        <th>Validé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subject_stats['moyennes_etudiants'] as $student_avg): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student_avg['nom'] . ' ' . $student_avg['prenom']); ?></td>
                                            <td><?php echo htmlspecialchars($student_avg['moyenne']); ?></td>
                                            <td><?php echo $student_avg['valide'] ? 'Oui' : 'Non'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="?page=dashboard_prof" class="btn btn-back">Retour au tableau de bord</a>
</div>

<style>
    .page-container {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    .stats-section {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
    .stats-section h3 {
        color: #0056b3;
        margin-top: 0;
        border-bottom: 2px solid #0056b3;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .stats-section h4 {
        color: #333;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    .stats-overview ul {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }
    .stats-overview ul li {
        background-color: #e9ecef;
        padding: 10px 15px;
        border-radius: 5px;
        font-size: 0.95em;
    }
    .stats-overview ul li strong {
        color: #007bff;
    }
    .stats-distribution table, .stats-details table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .stats-distribution th, .stats-details th {
        background-color: #007bff;
        color: white;
        padding: 10px;
        text-align: left;
        border: 1px solid #dee2e6;
    }
    .stats-distribution td, .stats-details td {
        padding: 8px 10px;
        border: 1px solid #dee2e6;
    }
    .stats-distribution tbody tr:nth-child(even), .stats-details tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .stats-distribution tbody tr:hover, .stats-details tbody tr:hover {
        background-color: #e2f0ff;
    }
    .message.info {
        background-color: #e7f3fe;
        color: #0056b3;
        border-color: #b8daff;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .btn-back {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    .btn-back:hover {
        background-color: #5a6268;
    }
    hr {
        border: 0;
        height: 1px;
        background-color: #e0e0e0;
        margin: 30px 0;
    }
</style>