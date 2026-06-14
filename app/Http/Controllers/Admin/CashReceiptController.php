<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashReceipt;
use App\Models\Contract;
use App\Models\User;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CashReceiptController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        return view('admin.business.cash_receipts', [
            'canViewCashReceipts' => (bool) ($user && $user->can('view-cash-receipts')),
            'canAcknowledgeCashReceipt' => (bool) ($user && $user->can('ack-cash-receipt')),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-cash-receipts')) {
            abort(403);
        }

        $validated = $request->validate([
            'received_from_user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric'],
            'received_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $receivedFromName = User::query()->whereKey((int) $validated['received_from_user_id'])->value('name');

        $receipt = CashReceipt::create([
            'related_type' => null,
            'related_value' => null,
            'received_by_user_id' => (int) $user->id,
            'amount' => (int) $validated['amount'],
            'settled_amount' => null,
            'received_from_name' => $receivedFromName,
            'received_date' => isset($validated['received_date']) && $validated['received_date'] ? $validated['received_date'] : now()->toDateString(),
            'status' => 'pending',
            'acknowledged_by' => null,
            'acknowledged_at' => null,
            'note' => isset($validated['note']) ? trim((string) $validated['note']) : null,
        ]);

        return response()->json([
            'message' => 'Sikeresen létrehozva!',
            'id' => $receipt->id,
        ], 201);
    }

    public function data(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-cash-receipts')) {
            abort(403);
        }

        $filters = [
            'related_type' => $request->input('filter_related_type'),
            'received_from_name' => $request->input('filter_received_from_name'),
            'received_by_name' => $request->input('filter_received_by_name'),
            'note' => $request->input('filter_note'),
            'created_at_from' => $request->input('filter_created_at_from'),
            'created_at_to' => $request->input('filter_created_at_to'),
            'status' => $request->input('filter_status'),
            'acknowledged_by_name' => $request->input('filter_acknowledged_by_name'),
            'acknowledged_at_from' => $request->input('filter_acknowledged_at_from'),
            'acknowledged_at_to' => $request->input('filter_acknowledged_at_to'),
        ];

        $query = CashReceipt::query()
            ->leftJoin('users as received_by_user', 'received_by_user.id', '=', 'cash_receipts.received_by_user_id')
            ->leftJoin('users as acknowledged_by_user', 'acknowledged_by_user.id', '=', 'cash_receipts.acknowledged_by')
            ->select([
                'cash_receipts.id',
                'cash_receipts.related_type',
                'cash_receipts.related_value',
                'cash_receipts.received_by_user_id',
                'cash_receipts.amount',
                'cash_receipts.settled_amount',
                'cash_receipts.received_from_name',
                'cash_receipts.received_date',
                'cash_receipts.status',
                'cash_receipts.acknowledged_by',
                'cash_receipts.acknowledged_at',
                'cash_receipts.note',
                'received_by_user.name as received_by_user_name',
                'acknowledged_by_user.name as acknowledged_by_user_name',
            ])
            ->groupBy('cash_receipts.id')
            ->with([
                'receivedBy:id,name',
                'acknowledgedBy:id,name',
                'related',
            ]);

        $query->when(is_string($filters['received_from_name']) && trim($filters['received_from_name']) !== '', function (Builder $q) use ($filters) {
            $q->where('cash_receipts.received_from_name', 'like', '%' . trim($filters['received_from_name']) . '%');
        });

        $query->when(is_string($filters['received_by_name']) && trim($filters['received_by_name']) !== '', function (Builder $q) use ($filters) {
            $q->where('received_by_user.name', 'like', '%' . trim($filters['received_by_name']) . '%');
        });

        $query->when(is_string($filters['acknowledged_by_name']) && trim($filters['acknowledged_by_name']) !== '', function (Builder $q) use ($filters) {
            $q->where('acknowledged_by_user.name', 'like', '%' . trim($filters['acknowledged_by_name']) . '%');
        });

        $query->when(is_string($filters['note']) && trim($filters['note']) !== '', function (Builder $q) use ($filters) {
            $q->where('cash_receipts.note', 'like', '%' . trim($filters['note']) . '%');
        });

        $query->when(is_string($filters['status']) && trim($filters['status']) !== '', function (Builder $q) use ($filters) {
            $q->where('cash_receipts.status', trim($filters['status']));
        });

        $query->when(is_string($filters['related_type']) && trim($filters['related_type']) !== '', function (Builder $q) use ($filters) {
            $val = trim($filters['related_type']);
            if ($val === 'contract') {
                $q->where('cash_receipts.related_type', Contract::class);
            }
            if ($val === 'worksheet') {
                $q->where('cash_receipts.related_type', Worksheet::class);
            }
            if ($val === 'other') {
                $q->whereNull('cash_receipts.related_type');
            }
        });

        $query->when(
            is_string($filters['created_at_from']) && trim($filters['created_at_from']) !== '' && is_string($filters['created_at_to']) && trim($filters['created_at_to']) !== '',
            function (Builder $q) use ($filters) {
                $from = trim($filters['created_at_from']) . ' 00:00:00';
                $to = trim($filters['created_at_to']) . ' 23:59:59';
                $q->whereBetween('cash_receipts.created_at', [$from, $to]);
            }
        );

        $query->when(
            is_string($filters['created_at_from']) && trim($filters['created_at_from']) !== '' && (!is_string($filters['created_at_to']) || trim($filters['created_at_to']) === ''),
            function (Builder $q) use ($filters) {
                $q->where('cash_receipts.created_at', '>=', trim($filters['created_at_from']) . ' 00:00:00');
            }
        );

        $query->when(
            is_string($filters['created_at_to']) && trim($filters['created_at_to']) !== '' && (!is_string($filters['created_at_from']) || trim($filters['created_at_from']) === ''),
            function (Builder $q) use ($filters) {
                $q->where('cash_receipts.created_at', '<=', trim($filters['created_at_to']) . ' 23:59:59');
            }
        );

        $query->when(
            is_string($filters['acknowledged_at_from']) && trim($filters['acknowledged_at_from']) !== '' && is_string($filters['acknowledged_at_to']) && trim($filters['acknowledged_at_to']) !== '',
            function (Builder $q) use ($filters) {
                $from = trim($filters['acknowledged_at_from']) . ' 00:00:00';
                $to = trim($filters['acknowledged_at_to']) . ' 23:59:59';
                $q->whereBetween('cash_receipts.acknowledged_at', [$from, $to]);
            }
        );

        $query->when(
            is_string($filters['acknowledged_at_from']) && trim($filters['acknowledged_at_from']) !== '' && (!is_string($filters['acknowledged_at_to']) || trim($filters['acknowledged_at_to']) === ''),
            function (Builder $q) use ($filters) {
                $q->where('cash_receipts.acknowledged_at', '>=', trim($filters['acknowledged_at_from']) . ' 00:00:00');
            }
        );

        $query->when(
            is_string($filters['acknowledged_at_to']) && trim($filters['acknowledged_at_to']) !== '' && (!is_string($filters['acknowledged_at_from']) || trim($filters['acknowledged_at_from']) === ''),
            function (Builder $q) use ($filters) {
                $q->where('cash_receipts.acknowledged_at', '<=', trim($filters['acknowledged_at_to']) . ' 23:59:59');
            }
        );

        return DataTables::of($query)
            ->editColumn('related_type', function ($r) {
                if (!$r->related_type) {
                    return 'Egyéb';
                }

                $label = null;
                if ($r->related_type === Contract::class) {
                    $label = 'Szerződés';
                    $url = route('admin.contracts.show_products_to_contracts', ['id' => $r->related_value]);
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($label) . '</a>';
                }

                if ($r->related_type === Worksheet::class) {
                    $label = 'Munkalap';
                    $url = route('admin.worksheets.show_data_to_worksheet', ['id' => $r->related_value]);
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($label) . '</a>';
                }

                if ($r->related_type === Contract::class) {
                    return 'Szerződés';
                }

                if ($r->related_type === Worksheet::class) {
                    return 'Munkalap';
                }

                return $r->related_type;
            })
            ->editColumn('related_value', function ($r) {
                $related = $r->related;
                if ($related instanceof Contract) {
                    return (string) ($related->name ?? $r->related_value ?? '');
                }
                if ($related instanceof Worksheet) {
                    return (string) ($related->name ?? $r->related_value ?? '');
                }
                return (string) ($r->related_value ?? '');
            })
            ->editColumn('received_date', fn($r) => $r->received_date ? \Carbon\Carbon::parse($r->received_date)->format('Y-m-d') : '')
            ->editColumn('amount', function ($r) {
                if ($r->amount === null || $r->amount === '') {
                    return '';
                }

                $amount = (float) $r->amount;
                return number_format($amount, 0, ',', ' ') . ' Ft';
            })
            ->editColumn('acknowledged_at', fn($r) => $r->acknowledged_at ? \Carbon\Carbon::parse($r->acknowledged_at)->format('Y-m-d H:i:s') : '')
            ->editColumn('status', function ($r) {
                $map = [
                    'pending' => 'Függőben',
                    'acknowledged' => 'Nyugtázva',
                ];
                return $map[$r->status] ?? (string) ($r->status ?? '');
            })
            ->addColumn('amount_raw', fn($r) => $r->amount)
            ->addColumn('received_by_name', fn($r) => $r->received_by_user_name ?? ($r->receivedBy?->name ?? '-'))
            ->addColumn('acknowledged_by_name', fn($r) => $r->acknowledged_by_user_name ?? ($r->acknowledgedBy?->name ?? '-'))
            ->rawColumns(['related_type'])
            ->make(true);
    }

    public function acknowledge(Request $request, CashReceipt $receipt)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('ack-cash-receipt')) {
            abort(403);
        }

        if ($receipt->status !== 'pending') {
            return response()->json([
                'message' => 'Ez a tétel már nem nyugtázható.',
            ], 422);
        }

        $validated = $request->validate([
            'settled_amount' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
        ]);

        $settledAmount = null;
        if (array_key_exists('settled_amount', $validated) && $validated['settled_amount'] !== null && $validated['settled_amount'] !== '') {
            $settledAmount = (int) $validated['settled_amount'];
        }

        $note = null;
        if (array_key_exists('note', $validated) && $validated['note'] !== null) {
            $note = trim((string) $validated['note']);
            if ($note === '') {
                $note = null;
            }
        }

        $receipt->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'settled_amount' => $settledAmount,
            'note' => $note,
        ]);

        return response()->json([
            'message' => 'Sikeresen nyugtázva!',
        ], 200);
    }

    public function bulkAcknowledge(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('ack-cash-receipt')) {
            abort(403);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'values' => ['nullable', 'array'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $values = isset($validated['values']) && is_array($validated['values']) ? $validated['values'] : [];

        $updatedCount = 0;

        DB::transaction(function () use ($ids, $values, $user, &$updatedCount) {
            $receipts = CashReceipt::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            foreach ($receipts as $receipt) {
                if ($receipt->status !== 'pending') {
                    continue;
                }

                $rowValues = isset($values[$receipt->id]) && is_array($values[$receipt->id]) ? $values[$receipt->id] : [];

                $settledAmount = null;
                if (array_key_exists('settled_amount', $rowValues) && $rowValues['settled_amount'] !== null && $rowValues['settled_amount'] !== '') {
                    if (is_numeric($rowValues['settled_amount'])) {
                        $settledAmount = (int) $rowValues['settled_amount'];
                    }
                }

                $note = null;
                if (array_key_exists('note', $rowValues) && $rowValues['note'] !== null) {
                    $note = trim((string) $rowValues['note']);
                    if ($note === '') {
                        $note = null;
                    }
                }

                $receipt->update([
                    'status' => 'acknowledged',
                    'acknowledged_by' => $user->id,
                    'acknowledged_at' => now(),
                    'settled_amount' => $settledAmount,
                    'note' => $note,
                ]);

                $updatedCount++;
            }
        });

        return response()->json([
            'message' => "Sikeresen nyugtázva: {$updatedCount} tétel.",
            'updated_count' => $updatedCount,
        ], 200);
    }
}
