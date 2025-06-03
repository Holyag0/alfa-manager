<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    /**
     * Cache prefix para o service
     */
    protected string $cachePrefix = '';

    /**
     * Tempo de cache em minutos
     */
    protected int $cacheTime = 60;

    /**
     * Log de operações do service
     */
    protected function logOperation(string $operation, string $message, array $context = []): void
    {
        Log::info("[{$this->getServiceName()}] {$operation}: {$message}", $context);
    }

    /**
     * Log de erro do service
     */
    protected function logError(string $operation, string $message, array $context = []): void
    {
        Log::error("[{$this->getServiceName()}] {$operation}: {$message}", $context);
    }

    /**
     * Gerar chave de cache
     */
    protected function getCacheKey(string $key): string
    {
        return $this->cachePrefix . ':' . $key;
    }

    /**
     * Armazenar no cache
     */
    protected function cacheData(string $key, $data, ?int $minutes = null): void
    {
        $minutes = $minutes ?? $this->cacheTime;
        Cache::put($this->getCacheKey($key), $data, now()->addMinutes($minutes));
    }

    /**
     * Recuperar do cache
     */
    protected function getCachedData(string $key)
    {
        return Cache::get($this->getCacheKey($key));
    }

    /**
     * Remover do cache
     */
    protected function forgetCacheKey(string $key): void
    {
        Cache::forget($this->getCacheKey($key));
    }

    /**
     * Limpar todo cache do service
     */
    protected function clearServiceCache(): void
    {
        $keys = Cache::getRedis()->keys($this->cachePrefix . ':*');
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * Validar dados de entrada
     */
    protected function validateData(array $data, array $rules): array
    {
        $validator = validator($data, $rules);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Nome do service para logs
     */
    protected function getServiceName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Executar operação com log
     */
    protected function executeWithLog(string $operation, callable $callback, array $context = [])
    {
        try {
            $this->logOperation($operation, 'Iniciando operação', $context);
            
            $result = $callback();
            
            $this->logOperation($operation, 'Operação concluída com sucesso', $context);
            
            return $result;
        } catch (\Exception $e) {
            $this->logError($operation, 'Erro na operação: ' . $e->getMessage(), array_merge($context, [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]));
            
            throw $e;
        }
    }
    /**
     * Formatar dados para resposta
     */
    protected function formatResponse($data, string $message = 'Operação realizada com sucesso'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }
    /**
     * Formatar erro para resposta
     */
    protected function formatError(string $message, array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }
    /**
     * Executar operação com cache
     */
    protected function executeWithCache(string $cacheKey, callable $callback, ?int $minutes = null)
    {
        // Tentar recuperar do cache primeiro
        $cached = $this->getCachedData($cacheKey);
        if ($cached !== null) {
            $this->logOperation('cache_hit', "Cache encontrado para: {$cacheKey}");
            return $cached;
        }

        // Executar callback e cachear resultado
        $result = $callback();
        $this->cacheData($cacheKey, $result, $minutes);
        
        $this->logOperation('cache_miss', "Dados cacheados para: {$cacheKey}");
        
        return $result;
    }
    /**
     * Sanitizar dados de entrada
     */
    protected function sanitizeData(array $data): array
    {
        return collect($data)->map(function ($value) {
            if (is_string($value)) {
                return trim($value);
            }
            return $value;
        })->toArray();
    }
    /**
     * Validar se ID existe
     */
    protected function validateModelExists(string $modelClass, int $id): Model
    {
        $model = $modelClass::find($id);
        
        if (!$model) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Registro não encontrado para ID: {$id}"
            );
        }

        return $model;
    }
    /**
     * Executar operação em lote
     */
    protected function executeBatch(array $items, callable $callback, int $batchSize = 100): array
    {
        $results = [];
        $chunks = array_chunk($items, $batchSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $item) {
                try {
                    $results[] = $callback($item);
                } catch (\Exception $e) {
                    $this->logError('batch_operation', "Erro no item: " . json_encode($item) . " - " . $e->getMessage());
                    $results[] = null; // ou continue dependendo da lógica
                }
            }
        }

        return array_filter($results); // Remove nulls
    }
    /**
     * Converter dados para formato de API
     */
    protected function toApiFormat($data): array
    {
        if ($data instanceof Model) {
            return $data->toArray();
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->toArray();
        }

        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
            ];
        }

        return is_array($data) ? $data : [$data];
    }
    /**
     * Verificar permissões (para implementar com Spatie)
     */
    protected function checkPermission(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Se usar Spatie Permission
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        return true; // Por enquanto, permitir tudo
    }
    /**
     * Aplicar filtros dinamicamente
     */
    protected function applyFilters($query, array $filters, array $allowedFilters = []): mixed
    {
        foreach ($filters as $key => $value) {
            if (!in_array($key, $allowedFilters) || empty($value)) {
                continue;
            }

            switch ($key) {
                case 'search':
                    if (method_exists($query->getModel(), 'scopeSearch')) {
                        $query->search($value);
                    }
                    break;
                
                case 'status':
                    $query->where('status', $value);
                    break;
                
                case 'created_at_from':
                    $query->where('created_at', '>=', $value);
                    break;
                
                case 'created_at_to':
                    $query->where('created_at', '<=', $value);
                    break;
                
                default:
                    // Filtro genérico
                    if ($query->getModel()->isFillable($key)) {
                        $query->where($key, $value);
                    }
                    break;
            }
        }

        return $query;
    }

    /**
     * Obter configuração do service
     */
    protected function getConfig(string $key, $default = null)
    {
        $serviceKey = strtolower(str_replace('Service', '', $this->getServiceName()));
        return config("services.school.{$serviceKey}.{$key}", $default);
    }

    /**
     * Gerar slug único
     */
    protected function generateUniqueSlug(string $title, string $modelClass, string $column = 'slug'): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($modelClass::where($column, $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}