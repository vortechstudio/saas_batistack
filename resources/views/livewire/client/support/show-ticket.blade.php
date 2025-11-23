<div class="bg-white">
    <div class="grid grid-cols-3 gap-5">
        <div class="col-span-2">
            <div class="card bg-base-100 card-md shadow-sm mb-10">
                <div class="card-body">
                    <h1 class="text-xl font-bold text-slate-900 mb-2">{{ $ticket->subject }}</h1>
                    <div class="flex items-center text-sm text-slate-500 gap-4">
                        <span>Ticket #{{ substr($ticket->uuid, 0, 8) }}</span>
                        <span>•</span>
                        <span>{{ $ticket->category->getLabel() }}</span>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 card-md shadow-sm">
                <div class="card-body">
                    <div class="space-y-6 max-h-[350px] overflow-y-scroll" wire:poll.1s>
                        @foreach($ticket->messages as $message)
                            <div class="flex gap-4 {{ $message->user_id === Auth::id() ? 'flex-row-reverse' : '' }}">
                                <div class="flex-shrink-0">
                                    <img src="{{ $message->user->avatar }}" class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold {{ $message->user_id === Auth::id() ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}" alt="">
                                </div>
                                <div class="max-w-[85%] {{ $message->user_id === Auth::id() ? 'text-right' : '' }}">
                                    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 {{ $message->user_id === Auth::id() ? 'rounded-tr-none bg-blue-50/50' : 'rounded-tl-none' }}">
                                        <p class="text-slate-800 whitespace-pre-wrap">{!! str($message->content)->markdown()->sanitizeHtml() !!}</p>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $message->created_at->format('d/m/Y H:i') }} • {{ $message->user->name }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- REPLY BOX --}}
                    @if($ticket->status->value !== 'closed')
                        <div class="bg-white shadow rounded-lg p-6 mt-6">
                            <form wire:submit="reply">
                                {{ $this->form }}
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="bg-ovh-primary text-white px-6 py-2 rounded-md font-semibold hover:bg-ovh-dark transition">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-lg p-6 text-center text-gray-500 text-sm">
                            Ce ticket est fermé. Vous ne pouvez plus y répondre. Veuillez ouvrir un nouveau ticket si nécessaire.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-span-1">
            {{ $this->entryTicketList }}
            @if($ticket->assignedAgent)
                <div class="bg-white shadow rounded-lg p-6 flex items-center mt-5">
                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center mr-4">
                        <span class="font-bold text-slate-600">S</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-900">Support Batistack</p>
                        <p class="text-xs text-slate-500">Votre dossier est suivi par un agent.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
