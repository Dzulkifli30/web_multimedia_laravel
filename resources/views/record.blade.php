<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Recorder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        video {
            border: 1px solid black;
        }
        button {
            margin: 10px;
        }
        .video-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }
        .video-item {
            border: 1px solid black;
            padding: 10px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Video Recorder</h1>
    <video id="video" width="640" height="480" autoplay muted></video>
    <br>
    <button id="startButton">Start Recording</button>
    <button id="stopButton" disabled>Stop Recording</button>
    <br>
    <video id="preview" width="640" height="480" controls class="hidden"></video>
    <br>
    <button id="uploadButton" class="hidden">Upload</button>
    <button id="retryButton" class="hidden">Retry</button>
    <form id="videoForm" action="{{ route('upload') }}" method="post" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="file" name="video" id="videoFile">
        <button type="submit">Upload</button>
    </form>

    <h2>Recorded Videos</h2>
    <div class="video-container">
        @foreach ($videos as $video)
            <div class="video-item">
                <video width="320" height="240" controls>
                    <source src="{{ Storage::url($video->path) }}" type="video/webm">
                    Your browser does not support the video tag.
                </video>
                <p>{{ $video->filename }}</p>
            </div>
        @endforeach
    </div>

    <script>
        const video = document.getElementById('video');
        const preview = document.getElementById('preview');
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');
        const uploadButton = document.getElementById('uploadButton');
        const retryButton = document.getElementById('retryButton');
        const videoForm = document.getElementById('videoForm');
        const videoFile = document.getElementById('videoFile');

        let mediaRecorder;
        let recordedChunks = [];

        async function init() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                video.srcObject = stream;
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.ondataavailable = function(event) {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = function() {
                    const blob = new Blob(recordedChunks, { type: 'video/webm' });
                    recordedChunks = [];
                    const videoURL = URL.createObjectURL(blob);
                    preview.src = videoURL;
                    preview.classList.remove('hidden');
                    uploadButton.classList.remove('hidden');
                    retryButton.classList.remove('hidden');
                    const file = new File([blob], 'recording.webm', { type: 'video/webm' });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    videoFile.files = dataTransfer.files;
                };
            } catch (error) {
                console.error('Error accessing the camera', error);
            }
        }

        startButton.addEventListener('click', () => {
            mediaRecorder.start();
            startButton.disabled = true;
            stopButton.disabled = false;
            video.muted = true;  // Mute the video element during recording
        });

        stopButton.addEventListener('click', () => {
            mediaRecorder.stop();
            startButton.disabled = false;
            stopButton.disabled = true;
            video.muted = false;  // Unmute the video element after recording
        });

        uploadButton.addEventListener('click', () => {
            videoForm.submit();
        });

        retryButton.addEventListener('click', () => {
            preview.classList.add('hidden');
            uploadButton.classList.add('hidden');
            retryButton.classList.add('hidden');
            videoFile.value = null;
        });

        init();
    </script>
</body>
</html>
