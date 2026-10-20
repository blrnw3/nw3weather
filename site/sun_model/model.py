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

    needs_veg = False
    rules = {}
    decision_t = None

    @classmethod
    def load(cls, name):
        with open(os.path.join(MODEL_DIR, name + ".json")) as fh:
            spec = json.load(fh)
        if spec.get("type") == "regime":
            return RegimeModel(spec)
        return cls(spec)

    def prob(self, f):
        x = np.array([f[k] for k in self.features], dtype=float)
        z = float(((x - self.mean) / self.scale) @ self.coef) + self.intercept
        return 1.0 / (1.0 + math.exp(-max(min(z, 50), -50)))


class RegimeModel(object):
    """v3: one LR per sun regime. 'lowmorning' (before solar noon, el < el_max) leans on sky brightness and the sun
    disc; 'main' (higher sun, afternoon and evening) leans on the lit-vs-shadow state of the near vegetation."""

    def __init__(self, spec):
        self.version = spec["version"]
        self.parts = {k: LrModel(v) for k, v in spec["parts"].items()}
        self.el_max = float(spec["regime"]["lowmorning_el_max"])
        self.az_max = float(spec["regime"]["lowmorning_az_max"])
        self.rules = spec.get("rules", {})
        self.decision_t = spec.get("decision_t")
        self.features = sorted(set(k for m in self.parts.values() for k in m.features))
        self.needs_veg = any(k.startswith(("v_", "vL_", "sL_", "s_", "r_", "x_")) for k in self.features)

    def regime(self, f):
        return "lowmorning" if f["az"] < self.az_max and f["el"] < self.el_max else "main"

    def prob(self, f):
        return self.parts[self.regime(f)].prob(f)
