<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Your Personal access tokens') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm::rounded-lg">
                <div class="broder-b border-gray-200">
                    <p class="mb-8">
                        <x-jet-button>
                            <a href="{{ route('token.showForm')}}">
                                Create New Token
                            </a>
                        </x-jet-button>
                    </p>
                    @if (count($tokens) > 0)

                    <div class="flex flex-col">
                        <div class="my-2 overflow-x-auto sm::mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:round">
                                    <table class="min-w-full divide-y divide-gray-200 w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text">
                                                    Name
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text">
                                                    Last Used
                                                </th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">Delete</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray divide-gray-200">
                                            @foreach ($tokens as $token)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{$token->name}}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{$token->last_used_at ? $token->last_used_at->diffForHumans() : ''}}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <form method="POST"
                                                        action="{{ route('token.delete', ['token' =>$token->id]) }}">
                                                        @csrf
                                                        <x-jet-danger-button type="submit">
                                                            Delete
                                                        </x-jet-danger-button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-center">You dont have personal access tokens yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>