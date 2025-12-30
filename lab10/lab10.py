import matplotlib.pyplot as plt


def calc_cg_content(window: str) -> float:
    count = 0
    for c in window:
        if c == "C" or c == "G":
            count += 1
    return (count / len(window)) * 100.0


def calc_kappa_ic(window: str) -> float:
    n = len(window)
    chars = list(window)
    total_probability = 0.0

    max_u = n - 1
    for u in range(1, max_u + 1):
        matches = 0
        suffix_len = n - u

        for i in range(suffix_len):
            if chars[i] == chars[i + u]:
                matches += 1

        if suffix_len > 0:
            total_probability += (matches / suffix_len) * 100.0

    return total_probability / max_u


def plot_pattern(points, filename: str):
    xs = [p[0] for p in points]
    ys = [p[1] for p in points]

    plt.figure(figsize=(6.4, 4.8))
    plt.title("DNA Pattern (C+G% vs Kappa IC)")
    plt.xlabel("C+G %")
    plt.ylabel("Kappa IC")
    plt.xlim(0, 100)
    plt.ylim(0, 100)
    plt.scatter(xs, ys, s=25)
    plt.savefig(filename, dpi=150, bbox_inches="tight")
    plt.close()


def plot_center(avg_cg: float, avg_kappa: float, filename: str):
    plt.figure(figsize=(6.4, 4.8))
    plt.title("Center of Weight")
    plt.xlabel("Average C+G %")
    plt.ylabel("Average Kappa IC")
    plt.xlim(0, 100)
    plt.ylim(0, 100)

    plt.scatter([avg_cg], [avg_kappa], s=80)
    plt.text(avg_cg + 2, avg_kappa, f"({avg_cg:.2f}, {avg_kappa:.2f})")

    plt.savefig(filename, dpi=150, bbox_inches="tight")
    plt.close()


def main():
    s = "CGGACTGATCTATCTAAAAAAAAAAAAAAAAAAAAAAAAAAACGTAGCATCTATCGATCTATCTAGCGATCTATCTACTACG"
    window_size = 30

    print("Sequence:", s)
    print("Total Length:", len(s))
    print("Window Size:", window_size)
    print("---")

    cg_values = []
    kappa_values = []
    points = []

    windows = []
    b = s.encode("utf-8")
    for i in range(0, len(b) - window_size + 1):
        windows.append(b[i : i + window_size].decode("utf-8"))

    for i, window in enumerate(windows):
        cg = calc_cg_content(window)
        kappa = calc_kappa_ic(window)

        cg_values.append(cg)
        kappa_values.append(kappa)
        points.append((cg, kappa))

        if i == 0:
            print("Window 0:", window)
            print(f"CG%: {cg:.2f}")
            print(f"Kappa IC: {kappa:.2f}")

    avg_cg = sum(cg_values) / len(cg_values)
    avg_kappa = sum(kappa_values) / len(kappa_values)

    print("---")
    print(f"Calculated Average CG%:      {avg_cg:.2f}")
    print(f"Calculated Average Kappa IC: {avg_kappa:.2f}")

    plot_pattern(points, "pattern_chart.png")
    plot_center(avg_cg, avg_kappa, "center_chart.png")


if __name__ == "__main__":
    main()