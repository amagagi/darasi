<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold">🔔 Notifications</h2>
        <span class="px-2 py-1 bg-red-500 text-white rounded-full text-xs">
            {{ $unreadCount }} non lues
        </span>
    </div>
    
    @forelse($notifications as $notif)
    <div class="border-b py-3 hover:bg-gray-50">
        <div class="flex items-start gap-3">
            <div class="mt-1">
                @if($notif->type == 'cours') 🎓
                @elseif($notif->type == 'paiement') 💰
                @elseif($notif->type == 'certificat') 📜
                @else 🔔
                @endif
            </div>
            <div class="flex-1">
                <p class="font-semibold">{{ $notif->titre }}</p>
                <p class="text-sm text-gray-600">{{ $notif->message }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $notif->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
    @empty
    <p class="text-gray-500 text-center py-4">✨ Aucune notification non lue</p>
    @endforelse
    
    <div class="mt-4 text-center">
        <a href="#" class="text-primary text-sm hover:underline">
            Voir toutes les notifications →
        </a>
    </div>
</div>