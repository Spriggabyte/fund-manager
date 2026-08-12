<?php

namespace App\Policies;

use App\Models\FundPdfExport;
use App\Models\User;

class FundPdfExportPolicy
{
    /**
     * Only the user who requested the export may inspect its status.
     */
    public function view(User $user, FundPdfExport $export): bool
    {
        return $user->id === $export->user_id;
    }

    /**
     * Only the requesting user may download the finished PDF.
     */
    public function download(User $user, FundPdfExport $export): bool
    {
        return $user->id === $export->user_id;
    }
}
