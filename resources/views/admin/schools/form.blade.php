@extends('layouts.app')
@section('title', $school->exists ? 'Edit Sekolah' : 'Tambah Sekolah')
@section('content')
<a href="{{ route('admin.schools.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold mt-2 mb-4">{{ $school->exists ? 'Edit Sekolah' : 'Tambah Sekolah' }}</h1>

<form method="POST" action="{{ $school->exists ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
      class="bg-white border border-slate-200 rounded p-6 max-w-3xl space-y-4">
    @csrf
    @if($school->exists) @method('PUT') @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Nama Sekolah</label>
            <input name="name" value="{{ old('name', $school->name) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Jenjang</label>
            <select name="jenjang" class="w-full border border-slate-300 rounded px-3 py-2">
                @foreach(['SD','SMP','SMA','SMK','MI','MTs','MA'] as $j)
                    <option value="{{ $j }}" @selected(old('jenjang', $school->jenjang)===$j)>{{ $j }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Alamat</label>
        <textarea name="address" rows="2" required
                  class="w-full border border-slate-300 rounded px-3 py-2">{{ old('address', $school->address) }}</textarea>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Latitude</label>
            <input name="latitude" type="number" step="any" value="{{ old('latitude', $school->latitude) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Longitude</label>
            <input name="longitude" type="number" step="any" value="{{ old('longitude', $school->longitude) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Program</label>
            <select name="program" class="w-full border border-slate-300 rounded px-3 py-2">
                @foreach(['BOTH'=>'KPM & PPL','KPM'=>'KPM saja','PPL'=>'PPL saja'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('program', $school->program ?? 'BOTH')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Kuota KPM</label>
            <input name="kuota_kpm" type="number" min="0" value="{{ old('kuota_kpm', $school->kuota_kpm ?? 0) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Kuota PPL</label>
            <input name="kuota_ppl" type="number" min="0" value="{{ old('kuota_ppl', $school->kuota_ppl ?? 0) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kontak Person</label>
            <input name="contact_person" value="{{ old('contact_person', $school->contact_person) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. Telp</label>
            <input name="phone" value="{{ old('phone', $school->phone) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $school->is_active ?? true))>
        Aktif (dapat dipilih mahasiswa)
    </label>

    <div class="flex justify-end pt-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
            Simpan
        </button>
    </div>
</form>
@endsection
