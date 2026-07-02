<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des cours</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        h1 { color: #4f46e5; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4f46e5; color: white; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>📚 DARASI - Liste des cours</h1>
    <p>Généré le : {{ now()->format('d/m/Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Titre</th><th>Formateur</th><th>Catégorie</th>
                <th>Prix</th><th>Certifiant</th><th>Statut</th><th>Apprenants</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cours as $cours)
            <tr>
                <td>{{ $cours->id }}</td>
                <td>{{ $cours->titre }}</td>
                <td>{{ $cours->formateur?->prenom }} {{ $cours->formateur?->nom ?? '-' }}</td>
                <td>{{ $cours->categorie?->nom ?? '-' }}</td>
                <td>{{ number_format($cours->prix, 0, ',', ' ') }} FCFA</td>
                <td>{{ $cours->est_certifiant ? '✅ Oui' : '❌ Non' }}</td>
                <td>{{ ucfirst($cours->statut) }}</td>
                <td>{{ $cours->nb_apprenants }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer"><p>DARASI - Plateforme de formation en ligne</p></div>
</body>
</html>