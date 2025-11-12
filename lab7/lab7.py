import sys
import os
from collections import Counter

def extract_sequence(raw: str) -> str:
    seq = []
    for line in raw.splitlines():
        if line.startswith(">"):
            continue
        seq.append(line.strip())
    joined = "".join(seq).upper()

    cleaned = []
    for ch in joined:
        if ch in ("A", "C", "G", "T", "N"):
            cleaned.append(ch)
    return "".join(cleaned)

def read_sequence_from_path(path: str) -> str:
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        return extract_sequence(f.read())

def read_sequence_from_stdin() -> str:
    return extract_sequence(sys.stdin.read())

def find_tandem_repeats(seq: str, k_min: int, k_max: int):
    n = len(seq)
    hits = []

    i = 0
    while i + k_min * 2 <= n:
        advanced = False

        for k in range(k_min, k_max + 1):
            if i + 2 * k > n:
                break

            motif = seq[i:i+k]
            r = 1
            while i + k * (r + 1) <= n and seq[i + k * r : i + k * (r + 1)] == motif:
                r += 1

            if r >= 2:
                hits.append({
                    "start": i,
                    "k": k,
                    "motif": motif,
                    "repeats": r,
                })
                i += k * r
                advanced = True
                break

        if not advanced:
            i += 1

    return hits

def safe_stem(path: str) -> str:
    stem = os.path.splitext(os.path.basename(path))[0] or "seq"
    out = []
    for c in stem:
        out.append(c if c.isalnum() else "_")
    return "".join(out)

def plot_histogram_png(filename: str, title: str, x_labels, values):
    try:
        import matplotlib.pyplot as plt
    except Exception:
        return False

    plt.figure(figsize=(12, 6))
    if len(x_labels) == 0:
        plt.title(title)
        plt.savefig(filename, dpi=150, bbox_inches="tight")
        plt.close()
        return True

    xs = list(range(len(x_labels)))
    plt.bar(xs, values)
    plt.title(title)
    plt.ylabel("frequency")
    plt.xlabel("Category")
    plt.xticks(xs, x_labels, rotation=0)

    for i, v in enumerate(values):
        if v > 0:
            plt.text(i, v, str(v), ha="center", va="bottom")

    plt.savefig(filename, dpi=150, bbox_inches="tight")
    plt.close()
    return True

def plot_frequencies(hits):
    by_k = Counter(h["k"] for h in hits)
    xk = [str(k) for k in sorted(by_k.keys())]
    yk = [by_k[int(k)] for k in map(int, xk)]
    plot_histogram_png(
        "repeats_by_k.png",
        "Number of repeats per motif length k",
        xk,
        yk,
    )

    by_r = Counter(h["repeats"] for h in hits)
    xr = [str(r) for r in sorted(by_r.keys())]
    yr = [by_r[int(r)] for r in map(int, xr)]
    plot_histogram_png(
        "repeats_by_repeats.png",
        "Number of tandem repeats per multiplicity r",
        xr,
        yr,
    )

def plot_motif_barchart_r3(out_basename: str, display_name: str, hits):
    try:
        import matplotlib.pyplot as plt
    except Exception:
        return False

    count = Counter(h["motif"] for h in hits if h["repeats"] == 3)
    if not count:
        return True

    items = sorted(count.items(), key=lambda x: -x[1])
    labels = [m for (m, _) in items]
    values = [c for (_, c) in items]

    plt.figure(figsize=(11, max(2, 1 + 0.28 * len(labels))))
    y = list(range(len(labels)))

    plt.barh(y, values)
    plt.yticks(y, labels)
    plt.xlabel("Frequency")
    plt.ylabel("Motif (3–10 bases, r = 3)")
    plt.title(f"Tandem repeats r = 3 in {display_name}")

    for i, v in enumerate(values):
        plt.text(v, i, str(v), va="center")

    outfile = f"{out_basename}_r3_motifs.png"
    plt.savefig(outfile, dpi=150, bbox_inches="tight")
    plt.close()
    return True

def main():
    K_MIN = 3
    K_MAX = 10

    if len(sys.argv) > 1:
        seq = read_sequence_from_path(sys.argv[1])
        basename = safe_stem(sys.argv[1])
    else:
        seq = read_sequence_from_stdin()
        basename = "seq"

    hits = find_tandem_repeats(seq, K_MIN, K_MAX)

    plot_motif_barchart_r3(basename, basename, hits)
    plot_frequencies(hits)

    for tr in hits:
        total_len = tr["k"] * tr["repeats"]
        print(
            f"start: {tr['start']}, k: {tr['k']}, sequence: {tr['motif']}, repeats: {tr['repeats']}, total_len: {total_len}"
        )

if __name__ == "__main__":
    main()
