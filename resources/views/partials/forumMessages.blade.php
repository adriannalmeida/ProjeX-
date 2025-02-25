<div class="forum-messages" id="forumMessages">
    @if ($forumMessages->isEmpty())
        <div class="no-messages-forum">
            <p>No messages yet.</p>
        </div>
    @else
        @foreach ($messages as $message)
            <div class="message-item">
                <img src="{{ $message->getAccount->getAccountImage() }}" class="round-photo" alt="Profile Picture">
                <div class="message-header">
                    <div class="account-info">
                        <p class="account-name"
                            onclick="window.location='{{ route('memberAccount.show', ['project' => $project->id, 'member' => $message->getAccount]) }}'">
                            {{ $message->getAccount->name }}</p>
                    </div>
                    <p class="message-date">{{ $message->create_date->format('d M Y, H:i') }}</p>
                </div>


                <div class="message-content">
                    <p>{{ $message->content }}</p>
                </div>

                @if ($message->getAccount->id === Auth::id())
                    <div class ="message-actions">

                        <button type="button" class="icon-button editMessageButton tooltip right"
                            data-text="Edit Message" id="edit-message" data-message-id="{{ $message->id }}">
                            <i class="fa fa-pencil"></i>
                        </button>

                        <form action="{{ route('forum.delete', ['message' => $message->id]) }}" method="POST"
                            class="confirmation">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-button confirm-action tooltip left"
                                data-text="Delete Message" id="deleteMessageButton">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @endif

                @if ($message->getAccount->id === Auth::id())
                    <form class="edit-message-form" style="display: none;"
                        action="{{ route('forum.edit', ['message' => $message->id]) }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <textarea class="message-input" name="message" rows="1" required>{{ $message->content }}</textarea>
                        <button type="submit" class="saveMessageButton">Save</button>
                        <button type="button" class="btn-cancel cancelEditMessage">Cancel</button>
                    </form>
                @endif
            </div>
        @endforeach
    @endif
</div>
