<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TranscodeProfile;
use Illuminate\Http\Request;

class TranscodeProfileController extends Controller
{
    /**
     * Display a listing of the transcode profiles.
     */
    public function index()
    {
        $profiles = TranscodeProfile::ordered()->paginate(15);

        return view('admin.transcode-profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new transcode profile.
     */
    public function create()
    {
        return view('admin.transcode-profiles.create');
    }

    /**
     * Store a newly created transcode profile in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $validated['is_active'] = $request->boolean('is_active', false);

        TranscodeProfile::create($validated);

        return redirect()
            ->route('admin.transcode-profiles.index')
            ->with('success', 'Transcode profile created successfully.');
    }

    /**
     * Display the specified transcode profile.
     */
    public function show(TranscodeProfile $transcodeProfile)
    {
        return view('admin.transcode-profiles.show', compact('transcodeProfile'));
    }

    /**
     * Show the form for editing the specified transcode profile.
     */
    public function edit(TranscodeProfile $transcodeProfile)
    {
        return view('admin.transcode-profiles.edit', compact('transcodeProfile'));
    }

    /**
     * Update the specified transcode profile in storage.
     */
    public function update(Request $request, TranscodeProfile $transcodeProfile)
    {
        $validated = $request->validate($this->validationRules($transcodeProfile->id));

        $validated['is_active'] = $request->boolean('is_active', false);

        $transcodeProfile->update($validated);

        return redirect()
            ->route('admin.transcode-profiles.index')
            ->with('success', 'Transcode profile updated successfully.');
    }

    /**
     * Remove the specified transcode profile from storage.
     */
    public function destroy(TranscodeProfile $transcodeProfile)
    {
        $transcodeProfile->delete();

        return redirect()
            ->route('admin.transcode-profiles.index')
            ->with('success', 'Transcode profile deleted successfully.');
    }

    /**
     * Get validation rules for transcode profile.
     *
     * @param int|null $ignoreId Profile ID to ignore for unique validation
     * @return array
     */
    private function validationRules(?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId 
            ? 'unique:transcode_profiles,name,'.$ignoreId
            : 'unique:transcode_profiles,name';

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:500'],
            'video_codec' => ['required', 'string', 'in:libx264,libx265,copy'],
            'video_bitrate' => ['nullable', 'string', 'max:20'],
            'video_width' => ['nullable', 'integer', 'min:100', 'max:7680'],
            'video_height' => ['nullable', 'integer', 'min:100', 'max:4320'],
            'video_fps' => ['nullable', 'integer', 'min:1', 'max:120'],
            'video_preset' => ['required_if:video_codec,libx264,libx265', 'in:ultrafast,superfast,veryfast,faster,fast,medium,slow,slower,veryslow'],
            'audio_codec' => ['required', 'string', 'in:aac,mp3,libmp3lame,copy'],
            'audio_bitrate' => ['nullable', 'string', 'max:20'],
            'audio_channels' => ['required', 'integer', 'in:1,2,6'],
            'audio_sample_rate' => ['nullable', 'integer', 'in:22050,44100,48000'],
            'container_format' => ['required', 'string', 'in:mpegts,hls,mp4'],
            'segment_duration' => ['nullable', 'integer', 'min:1', 'max:60'],
            'priority' => ['integer', 'min:0', 'max:100'],
        ];
    }
}
