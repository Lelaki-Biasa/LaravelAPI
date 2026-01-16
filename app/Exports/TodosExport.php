<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class TodosExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $todos;

    public function __construct($todos)
    {
        $this->todos = $todos;
    }

    public function collection()
    {
        return $this->todos;
    }

    public function headings(): array
    {
        return [
            'Title',
            'Assignee',
            'Due Date',
            'Time Tracked',
            'Status',
            'Priority'
        ];
    }

    public function map($todos): array
    {
        return [
            $todos->title,
            $todos->assignee,
            $todos->due_date,
            $todos->time_tracked,
            $todos->status,
            $todos->priority
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $row = $this->todos->count() + 2;
                
                $event->sheet->setCellValue("A{$row}", 'TOTAL');
                $event->sheet->setCellValue("D{$row}", $this->todos->sum('time_tracked'));
                $event->sheet->setCellValue("E{$row}", 'Total Todos : ' . $this->todos->count());
            },
        ];
    }

}
