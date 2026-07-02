<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }
        h1 {
            color: #4f46e5;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4f46e5;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>📋 DARASI - Liste des utilisateurs</h1>
    <p>Généré le : {{ now()->format('d/m/Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Date création</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->prenom }}</td>
                <td>{{ $user->nom }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->telephone ?? '-' }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>{{ $user->is_active ? '✅ Actif' : '❌ Inactif' }}</td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>DARASI - Plateforme de formation en ligne</p>
    </div>
</body>
</html>