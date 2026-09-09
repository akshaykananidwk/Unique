<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\Auth;
use App\Core\View;

abstract class Controller
{
    protected array $user;

    public function __construct()
    {
        $this->user = Auth::requireLogin();
    }

    protected function render(string $view, array $data = []): void
    {
        $data['user'] = $this->user;
        View::render($view, $data, 'layouts/admin');
    }

    /**
     * Send rows to the browser as a spreadsheet file.
     *
     * Excel reads a UTF-8 CSV only when it is told the encoding up front, so the byte-order
     * mark goes first — without it Gujarati names and the ₹ sign come out as rubbish.
     *
     * @param array<int,string> $headers
     * @param array<int,array<int,mixed>> $rows
     */
    protected function exportCsv(string $name, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '-' . date('Ymd-Hi') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, array_map(fn($v) => $v ?? '', $row));
        }
        fclose($out);
        exit;
    }

    protected function isManager(): bool
    {
        return in_array($this->user['role_slug'], ['super_admin', 'branch_manager'], true);
    }

    /**
     * Kept so every list query keeps its shape, but this is a single shop: there is one
     * Main Branch, nothing to choose between, and no ?branch_id to honour.
     *
     * @return array{0:string,1:array,2:null}
     */
    protected function branchScope(string $column): array
    {
        return ['1=1', [], null];
    }
}
