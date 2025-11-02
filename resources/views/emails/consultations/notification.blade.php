Olá {{ $user->name }},

O status da sua consulta #{{ $consultation->id }} foi atualizado.<br>

Título: {{ $consultation->title }}<br>
Novo Status: {{ $consultation->status }}<br>
Agendado para: {{ $consultation->scheduled_at->format('d/m/Y H:i:s') }}<br>

Obrigado,
