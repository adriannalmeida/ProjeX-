<div class="timeline">
    @if ($projectEvents->isEmpty())
        <div class="no-events">
            <p>No events found for this project.</p>
        </div>
    @else
        <ul class="list-group">
            @foreach ($projectEvents as $event)
                <li class="list-group-item">
                    <span class="event-icon">
                        @if ($event->type() === 'Task_Assigned')
                            <i class="fa fa-arrow-right"></i>
                        @elseif ($event->type() === 'Task_Unassigned')
                            <i class="fa fa-arrow-left"></i>
                        @elseif ($event->type() === 'Task_Created')
                            <i class="fa fa-plus"></i>
                        @elseif ($event->type() === 'Task_Completed')
                            <i class="fa fa-check"></i>
                        @elseif ($event->type() === 'Task_Deactivated')
                            <i class="fa fa-trash"></i>
                        @else
                            <i class="fa fa-calendar"></i>
                        @endif
                    </span>
                    <span class="event-account"
                        onclick="window.location='{{ route('memberAccount.show', ['project' => $project->id, 'member' => $event->account]) }}'">
                        {{ $event->accountInDescription() }}
                    </span>
                    &nbsp;{{ $event->description() }} on&nbsp;
                    <strong>{{ $event->time->format('F j, Y') }}</strong>
                </li>
            @endforeach
        </ul>
    @endif
</div>
