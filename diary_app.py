def main_menu():
    while True:
        print("\n=== Dream Notebook ===")
        print("1. Exit")

        choice = input("Choose an option: ")

        if choice == "1":
            print("Goodbye!")
            break
        else:
            print("Invalid choice.")


if __name__ == "__main__":
    main_menu()
