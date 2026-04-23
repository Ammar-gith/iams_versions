@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-start align-items-center">
                <h5 class="me-3">Notifications List</h5>
                @if ($notifications->isEmpty())
                    <span class="text-muted">No notifications to show</span>
                @endif
            </div>

            {{-- Get the authenticated logged in user --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            @if ($notifications->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Date Time</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($notifications as $key => $notification)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $notification->data['title'] }}</td>
                                    <td>{{ $notification->data['message'] }}</td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>{{ $notification->read_at ? 'Read' : 'Unread' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="custom-pagination">
                        {{ $notifications->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endpush