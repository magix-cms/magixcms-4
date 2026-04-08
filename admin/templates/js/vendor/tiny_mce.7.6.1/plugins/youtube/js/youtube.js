/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 #
 # This file is part of tinyMCE.
 # YouTube for tinyMCE 7.x (Magix CMS 4)
 # Copyright (C) 2008 - 2026  Gerits Aurelien (Magix CMS).
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.

 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <http://www.gnu.org/licenses/>.
 #
 # -- END LICENSE BLOCK -----------------------------------
 * https://developers.google.com/youtube/player_parameters
 */
class Youtube {
    constructor(url, options = {}) {
        this.id = this.idFromUrl(url);
        this.width = '';
        this.height = '';
        this.ratio = '16by9';
        this.autoplay = false;
        this.related = false;
        this.fullscreen = true;
        this.hd = false;
        if(typeof options === 'object') this.set(options);
        this.url = this.setSrcUrl();
        this.placeholder = this.setPlaceholderUrl();
    }

    set(options) {
        let instance = this;
        for (var key in options) {
            if (options.hasOwnProperty(key)) instance[key] = options[key];
        }
    }

    idFromUrl(url) {
        let match = url.match((/^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/));
        return match && match[2].length === 11 ? match[2] : false;
    }

    setSrcUrl() {
        return "https://www.youtube.com/embed/"+this.id+"?rel=" + (this.related ? '1' : '0')+"&autoplay=" + (this.autoplay ? '1' : '0');
    }

    setPlaceholderUrl() {
        return {
            jpeg: "https://i.ytimg.com/vi/"+this.id+"/",
            webp: "https://i.ytimg.com/vi_webp/"+this.id+"/"
        };
    }

    createIframe(width = 750, height = 315) {
        // Suppression des anciennes classes Bootstrap 3/4 pour l'aperçu du dialogue
        return '<iframe src="' + this.url + '" width="' + width + '" height="' + height + '" frameborder="0" allowfullscreen>&nbsp;</iframe>';
    }

    compiledHTML() {
        let params = {
            videoId: this.id,
            height: this.height,
            width: this.width,
            playerVars: {
                'autoplay': this.autoplay ? 1 : 0,
                'rel': this.related ? 1 : 0,
                'fs': this.fullscreen ? 1 : 0,
                'hd': this.hd ? 1 : 0
            }
        };

        let cssRatio = this.ratio.replace('by', ' / ').replace('x', ' / ');

        // UTILISATION DES CLASSES PURES (Plus de style="")
        let html = '<div class="magix-ytb-container mb-3" data-ratio="'+cssRatio+'">';
        html += '<picture class="ytb-video-preview" data-ytb=\''+JSON.stringify(params)+'\'>';

        html += '<source type="image/webp" sizes="(min-width: 640px) 1280px" srcset="'+this.placeholder.webp+'maxresdefault.webp 1280w">';
        html += '<source type="image/webp" sizes="(min-width: 480px) 640px" srcset="'+this.placeholder.webp+'hqdefault.webp 640w">';
        html += '<source type="image/webp" sizes="(min-width: 320px) 480px" srcset="'+this.placeholder.webp+'mqdefault.webp 480w">';
        html += '<source type="image/webp" sizes="(min-width: 120px) 320px" srcset="'+this.placeholder.webp+'sddefault.webp 320w">';
        html += '<source type="image/webp" sizes="120px" srcset="'+this.placeholder.webp+'default.webp 120w">';

        html += '<source type="image/jpeg" sizes="(min-width: 640px) 1280px" srcset="'+this.placeholder.jpeg+'maxresdefault.jpg 1280w">';
        html += '<source type="image/jpeg" sizes="(min-width: 480px) 640px" srcset="'+this.placeholder.jpeg+'hqdefault.jpg 640w">';
        html += '<source type="image/jpeg" sizes="(min-width: 320px) 480px" srcset="'+this.placeholder.jpeg+'mqdefault.jpg 480w">';
        html += '<source type="image/jpeg" sizes="(min-width: 120px) 320px" srcset="'+this.placeholder.jpeg+'sddefault.jpg 320w">';
        html += '<source type="image/jpeg" sizes="120px" srcset="'+this.placeholder.jpeg+'default.jpg 120w">';

        // Nouvelle classe magix-ytb-cover
        html += '<img src="'+this.placeholder.jpeg+'default.jpg" srcset="'+this.placeholder.jpeg+'maxresdefault.webp 1280w, '+this.placeholder.jpeg+'maxresdefault.webp 640w, '+this.placeholder.jpeg+'maxresdefault.webp 480w, '+this.placeholder.jpeg+'maxresdefault.webp 320w, '+this.placeholder.jpeg+'maxresdefault.webp 120w" sizes="(min-width: 640px) 1280px, (min-width: 480px) 640px, (min-width: 320px) 480px, (min-width: 120px) 320px, 120px" alt="Vidéo YouTube" loading="lazy" class="img-fluid magix-ytb-cover lazyload" data-ytb=\''+JSON.stringify(params)+'\'>';

        html += '</picture>';
        html += '</div><p><br></p>';

        return html;
    }
}

window.addEventListener('load',() => {
    let timer;
    let preview;
    let urlInput;
    let editor = parent.tinymce.activeEditor;
    let langVariables = {
        section_video: editor.translate("section_video"),
        aspect_video: editor.translate("aspect_video"),
        option_video: editor.translate("option_video"),
        preview_video: editor.translate("preview_video"),
        youtubeUrl: editor.translate("Youtube URL"),
        youtubeID: editor.translate("Youtube ID"),
        youtubeWidth: editor.translate("width"),
        youtubeHeight: editor.translate("height"),
        youtubeRatio: editor.translate("ratio"),
        ratio16by9: editor.translate("ratio16by9"),
        ratio4by3: editor.translate("ratio4by3"),
        youtubeAutoplay: editor.translate("autoplay"),
        youtubeHD: editor.translate("HD video"),
        youtubeREL: editor.translate("Related video"),
        cancel: editor.translate("cancel"),
        Insert: editor.translate("Insert")
    };

    /**
     * Display iframe preview
     */
    function renderPreview() {
        let url = urlInput.value;
        let youtube = new Youtube(url);
        preview.innerHTML = youtube.createIframe();
    }

    /**
     * Update Timer with keypress
     * @param ts {number} (optional)
     */
    function updateTimer(ts) {
        clearTimeout(timer);
        timer = setTimeout(renderPreview, ts || 1000);
    }

    /**
     * Init url input and preview render
     */
    function init() {
        preview = document.getElementById('preview');
        urlInput = document.getElementById('youtubeID');

        if(preview !== null && urlInput !== null) {
            urlInput.addEventListener('keypress',updateTimer);
            urlInput.addEventListener('change',() => { updateTimer(100); });
        }
    }

    /**
     * Insert content when the window form is submitted
     * @returns {string}
     */
    function renderHtml() {
        let video = urlInput.value;
        let html = '';
        if(video !== '') {
            let youtube = new Youtube(video, {
                autoplay: document.getElementById("youtubeAutoplay").checked,
                related: document.getElementById("youtubeREL").checked,
                hd: document.getElementById("youtubeHD").checked,
                width: document.getElementById("youtubeWidth").value,
                height: document.getElementById("youtubeHeight").value,
                ratio: document.getElementById("youtubeRatio").value
            });
            html = youtube.compiledHTML();
        }
        return html;
    }

    /**
     * Execute insert
     */
    function insert() {
        let html = renderHtml();
        parent.tinymce.activeEditor.insertContent(html);
        parent.tinymce.activeEditor.windowManager.close();
    }

    /**
     * Display the form into the dialog
     */
    fetch('./view/form.html').then((response) => {
        return response.text();
    }).then((template) => {
        document.getElementById('template-container').innerHTML = Mustache.render(template, langVariables);
        init();
        document.getElementById('insert-btn').addEventListener('click',insert);
        document.getElementById('close-btn').addEventListener('click',() => {
            parent.tinymce.activeEditor.windowManager.close();
        });
    });
});