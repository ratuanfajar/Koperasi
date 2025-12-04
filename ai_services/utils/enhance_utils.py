from PIL import Image as PILImage
import torch
import os
import requests
import numpy as np
import cv2
from basicsr.archs.srvgg_arch import SRVGGNetCompact
from realesrgan import RealESRGANer
from config.settings import REAL_ESRGAN_MODEL


def ensure_realesrgan_model(model_path=REAL_ESRGAN_MODEL):
    if not os.path.exists(model_path):
        os.makedirs(os.path.dirname(model_path), exist_ok=True)
        url = 'https://github.com/xinntao/Real-ESRGAN/releases/download/v0.2.5.0/realesr-general-x4v3.pth'
        print('Downloading RealESRGAN model...')
        r = requests.get(url, stream=True)
        r.raise_for_status()
        with open(model_path, 'wb') as f:
            for chunk in r.iter_content(1024 * 1024):
                f.write(chunk)
        print('Model downloaded')


def enhance_resolution(img_pil):
    device = 'cuda' if torch.cuda.is_available() else 'cpu'

    model = SRVGGNetCompact(
        num_in_ch=3, num_out_ch=3,
        num_feat=64, num_conv=32,
        upscale=4, act_type='prelu'
    )

    ensure_realesrgan_model()

    upsampler = RealESRGANer(
        scale=4,
        model_path=REAL_ESRGAN_MODEL,
        model=model,
        tile=0,
        tile_pad=10,
        pre_pad=0,
        half=False,
        device=torch.device(device)
    )

    img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)

    try:
        output, _ = upsampler.enhance(img_cv, outscale=4)
    except RuntimeError:
        # fallback
        upsampler.device = torch.device('cpu')
        output, _ = upsampler.enhance(img_cv, outscale=2)

    sr_image = PILImage.fromarray(cv2.cvtColor(output, cv2.COLOR_BGR2RGB))
    return sr_image