/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Article Parser - Native JavaScript
 * All images are downloaded server-side during preview
 */
document.addEventListener('DOMContentLoaded', () => {
  const elements = {
    urlBlock: document.querySelector('.url-block'),
    sourceInput: document.querySelector('.source_url'),
    imagesBlock: document.querySelector('.images-block'),
    btnSave: document.querySelector('.btn-save'),
    btnPreview: document.querySelector('.btn-preview'),
    contentBlock: document.querySelector('.parsed-content'),
    titleBlock: document.querySelector('.parsed-title'),
  };

  const preloader = {
    start(element) {
      const loader = document.createElement('div');
      loader.className = 'overlay';
      loader.innerHTML = '<i class="fas fa-2x fa-sync-alt fa-spin"></i>';
      element.appendChild(loader);
    },
    stop(element) {
      const overlay = element.querySelector('.overlay');
      if (overlay) overlay.remove();
    }
  };

  function getHeaders() {
    const csrfMeta = document.head.querySelector('[name="csrf-token"]');
    return {
      'x-csrf-token': csrfMeta ? csrfMeta.getAttribute('content') : '',
      'X-Requested-With': 'XMLHttpRequest'
    };
  }

  // Preview button - load article with all images downloaded server-side
  async function handlePreview() {
    preloader.start(elements.urlBlock);

    const sourceUrl = elements.sourceInput.value;
    const formData = new FormData();
    formData.append('source_url', encodeURIComponent(sourceUrl));

    try {
      const response = await fetch(elements.btnPreview.getAttribute('action'), {
        method: 'POST',
        headers: getHeaders(),
        body: formData
      });

      if (!response.ok) {
        throw new Error('HTTP error: ' + response.status);
      }

      const json = await response.json();

      if (json.status === 'success') {
        // Content already has local image URLs from server
        elements.contentBlock.innerHTML = json.data.content;
        elements.titleBlock.innerHTML = json.data.title;
        elements.btnSave.dataset.url = json.data.sourceUrl;

        // Show thumbnails of downloaded images in sidebar
        showImageThumbnails(json.data.imageUrls);

        preloader.stop(elements.urlBlock);
        showSuccess('Article and all images downloaded successfully.');
      } else {
        throw new Error(json.message + ': ' + (json.data || ''));
      }
    } catch (error) {
      preloader.stop(elements.urlBlock);
      showError(error.message);
    }
  }

  // Display thumbnails of downloaded images in sidebar
  function showImageThumbnails(imageUrls) {
    const cardTitle = elements.imagesBlock.querySelector('.card-title');
    const previewBlock = elements.imagesBlock.querySelector('.card-body');

    if (!previewBlock) return;

    // imageUrls is object {originalUrl: localUrl} or array
    const urls = typeof imageUrls === 'object' && !Array.isArray(imageUrls)
      ? Object.values(imageUrls)
      : imageUrls;

    const count = urls ? urls.length : 0;

    if (cardTitle) {
      cardTitle.textContent = `Post images (${count})`;
    }

    previewBlock.innerHTML = '';

    if (!urls || count === 0) return;

    urls.forEach((localUrl, index) => {
      const wrapper = document.createElement('div');
      wrapper.className = 'item';

      const badge = document.createElement('span');
      badge.className = 'notify-badge';
      badge.textContent = index + 1;

      const img = document.createElement('img');
      img.src = localUrl;
      img.className = 'img-size-64 img-thumbnail m-1';
      img.style.maxWidth = '64px';

      wrapper.appendChild(badge);
      wrapper.appendChild(img);
      previewBlock.appendChild(wrapper);
    });
  }

  // Save post - uses cached data, no additional downloads needed
  async function handleSavePost() {
    const sourceUrl = elements.btnSave.dataset.url;
    if (!sourceUrl) {
      showError('Please preview the article first');
      return;
    }

    preloader.start(elements.urlBlock);

    const formData = new FormData();
    formData.append('source_url', encodeURIComponent(sourceUrl));

    try {
      const response = await fetch(elements.btnSave.getAttribute('action'), {
        method: 'POST',
        headers: getHeaders(),
        body: formData
      });

      const json = await response.json();
      preloader.stop(elements.urlBlock);

      if (json.status === 'success') {
        showPostCreatedDialog(json.data);
      } else {
        throw new Error(json.message || 'Save failed');
      }
    } catch (error) {
      preloader.stop(elements.urlBlock);
      showError(error.message);
    }
  }

  function showSuccess(message) {
    if (typeof showAlert !== 'undefined') {
      showAlert({message: message, type: 'success'})
    }
  }

  function showError(message) {
    if (typeof showAlert !== 'undefined') {
      showAlert({message: message, type: 'error', duration: 0})
    } else {
      alert('Error: ' + message);
    }
  }

  function showPostCreatedDialog(postUrl) {

    if (typeof showAlert !== 'undefined') {
      showAlert({message: `Post created! URL: <a href="${postUrl}" style="color: white">${postUrl}</a>`, type: 'success', duration: 0})
    } else {
      alert('Post created! URL: ' + postUrl);
    }
  }

  elements.btnPreview.addEventListener('click', handlePreview);
  elements.btnSave.addEventListener('click', handleSavePost);
});
