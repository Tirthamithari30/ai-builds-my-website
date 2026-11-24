<x-app-layout :title="'Edit ' . $business->name . ' — AI Builds My Website'">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs & Header -->
        <div class="mb-6">
            <nav class="flex items-center text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('businesses.show', $business) }}" class="hover:text-slate-800">{{ $business->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-semibold">Edit</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Edit Business Details</h1>
            <p class="text-xs text-slate-500 mt-1">
                Update business information for {{ $business->name }}. Changes will be reflected across verified facts and website drafts.
            </p>
        </div>

        <form method="POST" action="{{ route('businesses.update', $business) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">1. Basic Information</h2>
                    <p class="text-xs text-slate-500">Core identity and business category</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700">Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $business->name) }}" required
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-semibold text-slate-700">Business Category</label>
                        <select id="category" name="category"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="restaurant" {{ old('category', $business->category) === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="cafe" {{ old('category', $business->category) === 'cafe' ? 'selected' : '' }}>Cafe / Coffee Shop</option>
                            <option value="salon" {{ old('category', $business->category) === 'salon' ? 'selected' : '' }}>Salon & Spa</option>
                            <option value="retail" {{ old('category', $business->category) === 'retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="consultant" {{ old('category', $business->category) === 'consultant' ? 'selected' : '' }}>Consulting & Advisory</option>
                            <option value="fitness" {{ old('category', $business->category) === 'fitness' ? 'selected' : '' }}>Fitness & Gym Centre</option>
                            <option value="professional_services" {{ old('category', $business->category) === 'professional_services' ? 'selected' : '' }}>Professional Services</option>
                            <option value="other" {{ old('category', $business->category) === 'other' ? 'selected' : '' }}>Other Business</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-slate-700">Business Description</label>
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $business->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact & Location -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">2. Contact & Location</h2>
                    <p class="text-xs text-slate-500">Public details for customers to reach you</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $business->phone) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700">Business Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $business->email) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-slate-700">Complete Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $business->address) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-semibold text-slate-700">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $business->city) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="state" class="block text-xs font-semibold text-slate-700">State / Province</label>
                        <input type="text" id="state" name="state" value="{{ old('state', $business->state) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="country" class="block text-xs font-semibold text-slate-700">Country</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $business->country) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Section 3: Operations & Offerings -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">3. Operations & Offerings</h2>
                    <p class="text-xs text-slate-500">Working hours, services, and product highlights</p>
                </div>

                @php
                    $rawHours = is_array($business->operating_hours) ? ($business->operating_hours['raw'] ?? '') : ($business->operating_hours ?? '');
                    $servicesText = is_array($business->services) ? implode("\n", array_map(fn($s) => is_string($s) ? $s : ($s['name'] ?? ''), $business->services)) : '';
                    $productsText = is_array($business->products) ? implode("\n", array_map(fn($p) => is_string($p) ? $p : ($p['name'] ?? ''), $business->products)) : '';
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="operating_hours" class="block text-xs font-semibold text-slate-700">Operating Hours</label>
                        <input type="text" id="operating_hours" name="operating_hours" value="{{ old('operating_hours', $rawHours) }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="services" class="block text-xs font-semibold text-slate-700">Services Offered <span class="text-slate-400 font-normal">(One per line)</span></label>
                        <textarea id="services" name="services" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('services', $servicesText) }}</textarea>
                    </div>

                    <div>
                        <label for="products" class="block text-xs font-semibold text-slate-700">Products Offered <span class="text-slate-400 font-normal">(One per line)</span></label>
                        <textarea id="products" name="products" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('products', $productsText) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="additional_info" class="block text-xs font-semibold text-slate-700">Additional Information</label>
                        <textarea id="additional_info" name="additional_info" rows="2"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('additional_info', $business->additional_info) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('businesses.show', $business) }}"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    Update Business Details
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
