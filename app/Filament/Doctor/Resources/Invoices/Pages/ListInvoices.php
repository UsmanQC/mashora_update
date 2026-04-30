<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Invoices\Pages;

use App\Filament\Doctor\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\ListRecords;

final class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;
}
