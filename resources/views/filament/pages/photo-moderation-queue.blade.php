<x-filament-panels::page>
    <div style="margin-bottom: 1rem;">
        <p style="font-size: 1.125rem; font-weight: 600; color: #374151;">
            {{ $this->pendingCount }} photo(s) pending review
        </p>
    </div>

    @if($this->pendingPhotos->isEmpty())
        <div style="display: flex; align-items: center; justify-content: center; padding: 3rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 3rem; height: 3rem; margin: 0 auto; color: #22c55e;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3 style="margin-top: 0.5rem; font-size: 1.125rem; font-weight: 500; color: #111827;">All caught up!</h3>
                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #6b7280;">No photos pending review.</p>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
            @foreach($this->pendingPhotos as $photo)
                <div style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;" wire:key="photo-{{ $photo->id }}">
                    <div style="aspect-ratio: 1; overflow: hidden; background: #f3f4f6;">
                        <img
                            src="{{ asset('storage/' . $photo->photo_url) }}"
                            alt="Photo by {{ $photo->user?->name }}"
                            style="width: 100%; height: 100%; object-fit: cover;"
                            loading="lazy"
                        />
                    </div>
                    <div style="padding: 1rem;">
                        <p style="font-size: 0.875rem; font-weight: 500; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $photo->user?->name ?? 'Unknown User' }}
                        </p>
                        <p style="font-size: 0.75rem; color: #6b7280;">
                            {{ $photo->created_at->diffForHumans() }}
                        </p>
                        <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                            <button
                                wire:click="approvePhoto({{ $photo->id }})"
                                wire:loading.attr="disabled"
                                style="flex: 1; display: inline-flex; justify-content: center; align-items: center; gap: 0.25rem; border-radius: 0.5rem; background: #16a34a; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: white; border: none; cursor: pointer;"
                                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                </svg>
                                Approve
                            </button>
                            <button
                                x-data=""
                                x-on:click="$dispatch('open-modal', { id: 'reject-photo-{{ $photo->id }}' })"
                                style="flex: 1; display: inline-flex; justify-content: center; align-items: center; gap: 0.25rem; border-radius: 0.5rem; background: #dc2626; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: white; border: none; cursor: pointer;"
                                onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                </svg>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Reject Modal --}}
                <x-filament::modal id="reject-photo-{{ $photo->id }}" heading="Reject Photo">
                    <form wire:submit="rejectPhoto({{ $photo->id }}, $event.target.querySelector('textarea').value)">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">
                                Rejection Reason
                            </label>
                            <textarea
                                name="reason"
                                rows="3"
                                style="display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem; font-size: 0.875rem;"
                                placeholder="Enter reason for rejection..."
                                required
                            ></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            <x-filament::button
                                color="danger"
                                type="submit"
                            >
                                Reject Photo
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::modal>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
