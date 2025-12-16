<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Feed geral de atividades
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->paginate(20)->through(function ($activity) {
            return $this->formatActivity($activity);
        });

        return Inertia::render('ActivityLog/Index', [
            'activities' => $activities,
            'filters' => $request->only(['user_id', 'date_from', 'date_to', 'log_name', 'event', 'search']),
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
            'logNames' => Activity::distinct()->pluck('log_name'),
            'events' => ['created', 'updated', 'deleted', 'restored'],
        ]);
    }

    /**
     * Feed de atividades de um usuário específico
     */
    public function userActivities(Request $request, $userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        // Buscar atividades do usuário OU atividades sem causer_id (sistema/automáticas)
        // Isso garante que atividades automáticas também apareçam no feed
        $query = Activity::with(['subject', 'causer'])
            ->where(function($q) use ($userId) {
                $q->where('causer_id', $userId)
                  ->orWhereNull('causer_id')
                  ->orWhere('causer_id', ''); // Também incluir causer_id vazio (string)
            })
            ->latest();

        // Filtros com validação
        if ($request->filled('date_from') && $request->date_from !== '') {
            try {
                $query->whereDate('created_at', '>=', $request->date_from);
            } catch (\Exception $e) {
                // Data inválida, ignorar filtro
            }
        }

        if ($request->filled('date_to') && $request->date_to !== '') {
            try {
                $query->whereDate('created_at', '<=', $request->date_to);
            } catch (\Exception $e) {
                // Data inválida, ignorar filtro
            }
        }

        if ($request->filled('time_from') && $request->time_from !== '') {
            try {
                $query->whereTime('created_at', '>=', $request->time_from);
            } catch (\Exception $e) {
                // Hora inválida, ignorar filtro
            }
        }

        if ($request->filled('time_to') && $request->time_to !== '') {
            try {
                $query->whereTime('created_at', '<=', $request->time_to);
            } catch (\Exception $e) {
                // Hora inválida, ignorar filtro
            }
        }

        if ($request->filled('log_name') && $request->log_name !== '') {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event') && $request->event !== '') {
            $query->where('event', $request->event);
        }

        $activities = $query->paginate(50)->through(function ($activity) {
            return $this->formatActivity($activity);
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * Lista de usuários com contagem de atividades
     */
    public function users()
    {
        $users = \App\Models\User::select('id', 'name', 'email')
            ->withCount('activities')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'activities_count' => $user->activities_count ?? 0,
                ];
            });

        return Inertia::render('ActivityLog/Users', [
            'users' => $users,
        ]);
    }

    /**
     * Estatísticas de atividades
     */
    public function stats()
    {
        return response()->json([
            'total_activities' => Activity::count(),
            'activities_today' => Activity::whereDate('created_at', today())->count(),
            'activities_this_week' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'activities_this_month' => Activity::whereMonth('created_at', now()->month)->count(),
            'top_users' => Activity::selectRaw('causer_id, COUNT(*) as count')
                ->whereNotNull('causer_id')
                ->where('causer_id', '!=', '')
                ->with('causer:id,name')
                ->groupBy('causer_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(fn($item) => [
                    'user' => $item->causer->name ?? 'Sistema',
                    'count' => $item->count,
                ]),
            'by_event' => Activity::selectRaw('COALESCE(event, \'sem_evento\') as event, COUNT(*) as count')
                ->groupBy('event')
                ->get()
                ->mapWithKeys(fn($item) => [$item->event ?? 'sem_evento' => $item->count]),
            'by_module' => Activity::selectRaw('log_name, COUNT(*) as count')
                ->groupBy('log_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->mapWithKeys(fn($item) => [$item->log_name => $item->count]),
        ]);
    }

    /**
     * Detalhes de uma atividade específica
     */
    public function show($id)
    {
        $activity = Activity::with(['causer', 'subject'])
            ->findOrFail($id);

        return response()->json([
            'activity' => $this->formatActivity($activity),
        ]);
    }

    /**
     * Formatar atividade para frontend
     */
    protected function formatActivity(Activity $activity): array
    {
        // Verificar se causer existe e é válido
        $causer = null;
        if ($activity->causer_id && $activity->causer_id !== '' && $activity->causer) {
            $causer = [
                'id' => $activity->causer->id,
                'name' => $activity->causer->name,
                'email' => $activity->causer->email,
            ];
        }

        // Verificar se subject existe e é válido
        $subjectType = null;
        if ($activity->subject_type) {
            try {
                if (class_exists($activity->subject_type)) {
                    $subjectType = class_basename($activity->subject_type);
                }
            } catch (\Exception $e) {
                // Classe não existe, manter null
            }
        }

        return [
            'id' => $activity->id,
            'description' => $activity->description ?? 'Sem descrição',
            'event' => $activity->event,
            'event_label' => $this->getEventLabel($activity->event ?? ''),
            'log_name' => $activity->log_name ?? 'default',
            'subject_type' => $subjectType,
            'subject_id' => $activity->subject_id,
            'causer' => $causer ?? [
                'id' => null,
                'name' => 'Sistema',
                'email' => null,
            ],
            'properties' => $this->safeGetProperties($activity),
            'changes' => $this->getChanges($activity),
            'created_at' => $activity->created_at,
            'created_at_human' => $activity->created_at ? $activity->created_at->diffForHumans() : 'Data inválida',
            'created_at_formatted' => $activity->created_at ? $activity->created_at->format('d/m/Y H:i:s') : 'Data inválida',
            'created_at_date' => $activity->created_at ? $activity->created_at->format('d/m/Y') : 'Data inválida',
            'created_at_time' => $activity->created_at ? $activity->created_at->format('H:i:s') : 'Hora inválida',
        ];
    }

    /**
     * Obter properties de forma segura
     */
    protected function safeGetProperties(Activity $activity): array
    {
        try {
            $properties = $activity->properties;
            if (is_array($properties)) {
                return $properties;
            }
            if (is_object($properties)) {
                return (array) $properties;
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter mudanças formatadas
     */
    protected function getChanges(Activity $activity): ?array
    {
        if (!$activity->properties || !isset($activity->properties['attributes'])) {
            return null;
        }

        $attributes = $activity->properties['attributes'] ?? [];
        $old = $activity->properties['old'] ?? [];

        if (empty($attributes) && empty($old)) {
            return null;
        }

        return [
            'old' => $old,
            'new' => $attributes,
        ];
    }

    /**
     * Label do evento
     */
    protected function getEventLabel(?string $event): string
    {
        if (empty($event)) {
            return 'Ação';
        }

        $labels = [
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Exclusão',
            'restored' => 'Restauração',
        ];

        return $labels[$event] ?? ucfirst($event);
    }
}

