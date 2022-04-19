import React from "react";

export function NewFeature() {
  return (
    <>
      <span className="feature new-feature">New Feature:</span>
    </>
  );
}

export function FixedBug() {
  return (
    <>
      <span className="feature fixed-bug">Fixed Bug:</span>
    </>
  );
}

export function UpdateFeature() {
  return (
    <>
      <span className="feature update-feature">Update:</span>
    </>
  );
}

export function ModuleName({ children }) {
  return (
    <>
        <span className="module-name">{children}</span>
    </>
  );
}
