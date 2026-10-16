"""Logistic-regression artifacts stored as small JSON (feature list, scaler mean/scale, coef, intercept)."""
import json
import math
import os

import numpy as np

MODEL_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "models")


class LrModel(object):
    def __init__(self, spec):
        self.version = spec["version"]
        self.features = spec["features"]
        self.mean = np.array(spec["mean"])
        self.scale = np.array(spec["scale"])
        self.coef = np.array(spec["coef"])
        self.intercept = float(spec["intercept"])

    @classmethod
    def load(cls, name):
        with open(os.path.join(MODEL_DIR, name + ".json")) as fh:
            return cls(json.load(fh))

    def prob(self, f):
        x = np.array([f[k] for k in self.features], dtype=float)
        z = float(((x - self.mean) / self.scale) @ self.coef) + self.intercept
        return 1.0 / (1.0 + math.exp(-max(min(z, 50), -50)))
